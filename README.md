# XBoard NOWPayments

A payment plugin for accepting cryptocurrency payments through NOWPayments hosted invoices. Incoming payment notifications (IPNs) are processed only after HMAC-SHA512 signature verification.

- Plugin version: `1.0.0`
- Plugin files: [`Nowpayments/`](Nowpayments/)
- Installation and configuration guide: [`Nowpayments/README.md`](Nowpayments/README.md)
- Release ZIP: [Download the latest version](https://github.com/ian-me/Xboard-nowpayments/releases/latest/download/Nowpayments-v1.0.0.zip)

## Features

- Creates hosted NOWPayments payment invoices and redirects customers to the payment page
- Verifies the HMAC-SHA512 signature in the `x-nowpayments-sig` header
- Marks an order as paid only when its status is `finished` by default
- Optionally accepts the `confirmed` status as a completed payment
- Supports the sandbox API, fixed payment currencies, exchange rates, and customer-paid fees

## Installation

1. Download the repository or extract the release ZIP, then copy the entire `Nowpayments` directory into the `plugins/` directory of your XBoard installation. The resulting paths should be:
   ```text
   plugins/Nowpayments/Plugin.php
   plugins/Nowpayments/config.json
   ```
2. In the XBoard admin panel, open **Plugins**, then install and enable **NOWPayments**.
3. Open **Payment Settings → Add → Payment Method**, select **NOWPayments**, and enter your settings.
4. In Docker environments, you may need to restart the container or run `php artisan octane:reload` for changes to take effect.

## Configuration

| Setting | Description |
|---|---|
| API Key | Required. Create one in the NOWPayments dashboard under **Store Settings → API keys**. |
| IPN Secret | Required. Create one under **Store Settings → Instant payment notifications**. |
| Price Currency | Currency code used for the invoice amount. Defaults to `usd`. |
| Exchange Rate | Amount of the Price Currency equal to one unit of your site's currency. Defaults to `1`. For example, enter `7.2` if your site currency is CNY and the Price Currency is USD. |
| Fixed Pay Currency | Set a currency such as `usdttrc20` to require a specific payment currency. Leave blank to let the customer choose. |
| Fee Paid By User | Set to `1` to have the customer pay the fee. Defaults to `0`. |
| Accept "confirmed" | Set to `1` to also treat `confirmed` as completed. By default, only `finished` is accepted. |
| Sandbox Mode | Set to `1` to use the sandbox API. A sandbox API key is required. Defaults to `0`. |

The payment amount is calculated by dividing XBoard's `total_amount` (in cents) by `100 × Exchange Rate`. XBoard supplies the IPN notification URL when the invoice is created, so you do not need to register it separately in the NOWPayments dashboard. The notification URL must be reachable from the internet.

## Payment Handling and Notes

Customers pay on the hosted NOWPayments invoice page. XBoard verifies the callback signature and completes an order only when the payment status is allowed and the order number and payment ID are valid. Statuses such as `waiting`, `confirming`, `partially_paid`, `failed`, and `expired` do not automatically complete an order. Check partial payments directly in the NOWPayments dashboard.

Use HTTPS in production and never expose your API Key or IPN Secret. We recommend testing the integration in sandbox mode before accepting live payments.

## License and Disclaimer

Distributed under the MIT License. This plugin is not officially provided or endorsed by XBoard or NOWPayments. See [`LICENSE`](LICENSE) for the full license terms.
