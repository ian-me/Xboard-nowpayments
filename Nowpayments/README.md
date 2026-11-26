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

---

## 简体中文

# 适用于 XBoard 的 NOWPayments 插件

本支付插件使用 NOWPayments 托管发票。客户会跳转到 NOWPayments 付款页面；IPN 回调通过 HMAC-SHA512 签名验证后，订单将自动完成。

## 安装

1. 将此 `Nowpayments` 目录复制到 XBoard 安装目录下的 `plugins/` 目录中。路径应为：
   ```text
   plugins/Nowpayments/Plugin.php
   plugins/Nowpayments/config.json
   ```
2. 在管理面板中打开 **Plugins（插件）**，然后安装并启用 **NOWPayments**。在 Docker 环境中，可能需要重启容器或运行 `php artisan octane:reload`。
3. 打开 **Payment Settings（支付设置）→ Add（添加）→ Payment Method（支付方式）**，选择 **NOWPayments**。

## NOWPayments 控制台设置

- 在 **Store Settings → API keys（商店设置 → API 密钥）** 下创建 API Key。
- 在 **Store Settings → Instant payment notifications（商店设置 → 即时付款通知）** 下创建 IPN Secret。
- 创建发票时，支付设置中显示的通知 URL（例如 `/api/v1/guest/payment/notify/NOWPayments/{uuid}`）会自动发送。无需在控制台中另行添加该 URL。

## 设置项

| 设置项 | 说明 |
|---|---|
| API Key / IPN Secret | 必填。 |
| Price Currency（计价币种） | 发票金额使用的币种，默认值为 `usd`。 |
| Exchange Rate（汇率） | 计价币种每 1 单位对应的网站币种金额。例如，若网站币种为 CNY、计价币种为 USD，可填写 `7.2`。默认值为 `1`。 |
| Fixed Pay Currency（固定付款币种） | 固定付款币种，例如 `usdttrc20`。留空则由客户选择。 |
| Fee Paid By User（由用户支付手续费） | 设置为 `1` 时由客户承担手续费。 |
| Accept "confirmed"（接受 confirmed 状态） | 设置为 `1` 时也会完成 `confirmed` 状态的订单。默认只接受 `finished`。 |
| Sandbox Mode（沙盒模式） | 设置为 `1` 时使用沙盒 API；必须提供沙盒 API 密钥。 |

## 行为说明

- 金额通过将 XBoard 的 `total_amount`（以分为单位）按 `/100/Exchange Rate` 换算得出。
- 除 `finished` 以外的状态（例如 `waiting`、`confirming` 或 `partially_paid`）不会完成订单。插件会返回失败响应，因此 NOWPayments 可能会重试通知；这是预期行为。
- 部分付款（`partially_paid`）不会自动完成订单，请直接在 NOWPayments 控制台中查看。
- 通知 URL 必须能从网络外部访问。如果在支付设置中使用通知域名（`notify_domain`），该域名必须路由至你的面板。
