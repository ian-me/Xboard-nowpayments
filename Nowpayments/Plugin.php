<?php

namespace Plugin\Nowpayments;

use App\Contracts\PaymentInterface;
use App\Services\Plugin\AbstractPlugin;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Plugin extends AbstractPlugin implements PaymentInterface
{
    private const API_LIVE = 'https://api.nowpayments.io/v1';
    private const API_SANDBOX = 'https://api-sandbox.nowpayments.io/v1';

    public function boot(): void
    {
        $this->filter('available_payment_methods', function ($methods) {
            $methods['NOWPayments'] = [
                'name' => $this->getConfig('display_name', 'NOWPayments (Crypto)'),
                'icon' => $this->getConfig('icon', '🪙'),
                'plugin_code' => 'nowpayments',
                'type' => 'plugin',
            ];
            return $methods;
        });
    }

    /**
     * Admin-side form shown when adding/editing this payment method.
     */
    public function form(): array
    {
        return [
            'api_key' => [
                'label' => 'API Key',
                'type' => 'string',
                'required' => true,
                'description' => 'NOWPayments dashboard > Store Settings > API keys',
            ],
            'ipn_secret' => [
                'label' => 'IPN Secret',
                'type' => 'string',
                'required' => true,
                'description' => 'NOWPayments dashboard > Store Settings > Instant payment notifications',
            ],
            'price_currency' => [
                'label' => 'Price Currency',
                'type' => 'string',
                'required' => false,
                'description' => 'Fiat/crypto code used for the invoice amount (default: usd)',
            ],
            'exchange_rate' => [
                'label' => 'Exchange Rate',
                'type' => 'string',
                'required' => false,
                'description' => 'Site currency units per 1 price currency. e.g. site in CNY and price currency usd -> 7.2. Default 1',
            ],
            'pay_currency' => [
                'label' => 'Fixed Pay Currency',
                'type' => 'string',
                'required' => false,
                'description' => 'Optional. Lock the coin, e.g. usdttrc20. Leave empty to let the user choose',
            ],
            'fee_paid_by_user' => [
                'label' => 'Fee Paid By User',
                'type' => 'string',
                'required' => false,
                'description' => '1 = customer pays the network/service fee, 0 = merchant pays (default 0)',
            ],
            'accept_confirmed' => [
                'label' => 'Accept "confirmed" Status',
                'type' => 'string',
                'required' => false,
                'description' => '1 = mark order paid on "confirmed" as well as "finished" (default 0, only "finished")',
            ],
            'sandbox' => [
                'label' => 'Sandbox Mode',
                'type' => 'string',
                'required' => false,
                'description' => '1 = use api-sandbox.nowpayments.io (needs sandbox keys), 0 = live (default 0)',
            ],
        ];
    }

    /**
     * Create a NOWPayments invoice and send the user to the hosted page.
     */
    public function pay($order): array
    {
        $apiKey = (string) $this->cfg('api_key', '');
        if ($apiKey === '') {
            throw new \Exception('NOWPayments: API key is not configured');
        }

        $rate = (float) $this->cfg('exchange_rate', 1);
        if ($rate <= 0) {
            $rate = 1.0;
        }

        // Xboard stores amounts in cents of the site currency.
        $amount = round(((float) $order['total_amount']) / 100 / $rate, 2);
        if ($amount < 0.01) {
            throw new \Exception('NOWPayments: order amount is too small');
        }

        $payload = [
            'price_amount' => $amount,
            'price_currency' => strtolower((string) $this->cfg('price_currency', 'usd')),
            'order_id' => (string) $order['trade_no'],
            'order_description' => 'Order ' . $order['trade_no'],
            'ipn_callback_url' => $order['notify_url'],
            'success_url' => $order['return_url'],
            'cancel_url' => $order['return_url'],
        ];

        $payCurrency = trim((string) $this->cfg('pay_currency', ''));
        if ($payCurrency !== '') {
            $payload['pay_currency'] = strtolower($payCurrency);
        }
        if ($this->isTruthy($this->cfg('fee_paid_by_user', '0'))) {
            $payload['is_fee_paid_by_user'] = true;
        }

        try {
            $response = Http::withHeaders(['x-api-key' => $apiKey])
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->post($this->apiBase() . '/invoice', $payload);
        } catch (\Throwable $e) {
            Log::error('NOWPayments invoice request failed', ['error' => $e->getMessage()]);
            throw new \Exception('NOWPayments: failed to reach the gateway');
        }

        $invoiceUrl = $response->json('invoice_url');
        if (!$response->successful() || empty($invoiceUrl)) {
            Log::error('NOWPayments invoice creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('NOWPayments: failed to create invoice');
        }

        return [
            'type' => 1, // 1 = redirect to URL
            'data' => $invoiceUrl,
        ];
    }

    /**
     * IPN callback. Verifies the HMAC-SHA512 signature and the payment status.
     * Returning false makes Xboard answer with a failure (order stays pending).
     */
    public function notify($params): array|bool
    {
        $secret = trim((string) $this->cfg('ipn_secret', ''));
        $signature = strtolower((string) request()->header('x-nowpayments-sig', ''));
        if ($secret === '' || $signature === '') {
            return false;
        }

        $body = json_decode(request()->getContent(), true);
        if (!is_array($body)) {
            return false;
        }

        // Signature = HMAC-SHA512 over the key-sorted (recursive) JSON body.
        $this->sortRecursive($body);
        $sorted = json_encode($body, JSON_UNESCAPED_SLASHES);
        $expected = hash_hmac('sha512', $sorted, $secret);
        if (!hash_equals($expected, $signature)) {
            Log::warning('NOWPayments IPN signature mismatch');
            return false;
        }

        $status = (string) ($body['payment_status'] ?? '');
        $accepted = ['finished'];
        if ($this->isTruthy($this->cfg('accept_confirmed', '0'))) {
            $accepted[] = 'confirmed';
        }
        if (!in_array($status, $accepted, true)) {
            // waiting / confirming / sending / partially_paid / failed / expired ...
            return false;
        }

        $tradeNo = (string) ($body['order_id'] ?? '');
        $paymentId = (string) ($body['payment_id'] ?? '');
        if ($tradeNo === '' || $paymentId === '') {
            return false;
        }

        return [
            'trade_no' => $tradeNo,
            'callback_no' => $paymentId,
            'custom_result' => 'OK',
        ];
    }

    private function cfg(string $key, mixed $default = null): mixed
    {
        $value = $this->getConfig($key);
        return ($value === null || $value === '') ? $default : $value;
    }

    private function apiBase(): string
    {
        return $this->isTruthy($this->cfg('sandbox', '0')) ? self::API_SANDBOX : self::API_LIVE;
    }

    private function isTruthy(mixed $value): bool
    {
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function sortRecursive(array &$array): void
    {
        ksort($array);
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->sortRecursive($value);
            }
        }
    }
}
