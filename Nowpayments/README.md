# NOWPayments for XBoard

A payment plugin that uses NOWPayments hosted invoices. Customers are redirected to the NOWPayments payment page, and orders are completed automatically after an IPN callback passes HMAC-SHA512 signature verification.

## Installation

1. Copy this `Nowpayments` directory into the `plugins/` directory of your XBoard installation. The paths should be:
   ```text
   plugins/Nowpayments/Plugin.php
   plugins/Nowpayments/config.json
   ```
2. In the admin panel, open **Plugins**, then install and enable **NOWPayments**. In Docker environments, you may need to restart the container or run `php artisan octane:reload`.
3. Open **Payment Settings → Add → Payment Method** and select **NOWPayments**.

## NOWPayments Dashboard Setup

- Create an API Key under **Store Settings → API keys**.
- Create an IPN Secret under **Store Settings → Instant payment notifications**.
- The notification URL shown in the payment settings (for example, `/api/v1/guest/payment/notify/NOWPayments/{uuid}`) is sent automatically when an invoice is created. You do not need to add it separately in the dashboard.

## Settings

| Setting | Description |
|---|---|
| API Key / IPN Secret | Required. |
| Price Currency | Currency used for the invoice amount. Defaults to `usd`. |
| Exchange Rate | Site currency per unit of the Price Currency. For example, enter `7.2` if the site currency is CNY and the Price Currency is USD. Defaults to `1`. |
| Fixed Pay Currency | Fixes the payment currency, for example `usdttrc20`. Leave blank to let the customer choose. |
| Fee Paid By User | Set to `1` to have the customer pay the fee. |
| Accept "confirmed" | Set to `1` to also complete orders with the `confirmed` status. By default, only `finished` is accepted. |
| Sandbox Mode | Set to `1` to use the sandbox API. A sandbox API key is required. |

## Behavior Notes

- The amount is calculated by converting XBoard's `total_amount` (in cents) using `/100/Exchange Rate`.
- A status other than `finished` (such as `waiting`, `confirming`, or `partially_paid`) does not complete the order. The plugin returns a failure response, so NOWPayments may retry the notification. This is expected behavior.
- Partial payments (`partially_paid`) are not completed automatically. Check them directly in the NOWPayments dashboard.
- The notification URL must be reachable from outside your network. If you use the notification domain (`notify_domain`) in the payment settings, that domain must route to your panel.
