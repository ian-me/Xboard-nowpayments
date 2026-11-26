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

---

## 简体中文

# XBoard NOWPayments

一款通过 NOWPayments 托管支付发票接收加密货币付款的插件。只有在 HMAC-SHA512 签名验证通过后，才会处理付款通知（IPN）。

- 插件版本：`1.0.0`
- 插件文件：[`Nowpayments/`](Nowpayments/)
- 安装与配置指南：[`Nowpayments/README.md`](Nowpayments/README.md)
- 发布 ZIP：[下载最新版本](https://github.com/ian-me/Xboard-nowpayments/releases/latest/download/Nowpayments-v1.0.0.zip)

## 功能

- 创建 NOWPayments 托管支付发票，并将客户跳转至付款页面
- 验证 `x-nowpayments-sig` 请求头中的 HMAC-SHA512 签名
- 默认仅在付款状态为 `finished` 时将订单标记为已付款
- 可选将 `confirmed` 状态也视为付款完成
- 支持沙盒 API、固定付款币种、汇率以及由客户承担手续费

## 安装

1. 下载仓库或解压发布 ZIP，然后将整个 `Nowpayments` 目录复制到 XBoard 安装目录下的 `plugins/` 目录中。复制后应包含以下路径：
   ```text
   plugins/Nowpayments/Plugin.php
   plugins/Nowpayments/config.json
   ```
2. 在 XBoard 管理面板中打开 **Plugins（插件）**，然后安装并启用 **NOWPayments**。
3. 打开 **Payment Settings（支付设置）→ Add（添加）→ Payment Method（支付方式）**，选择 **NOWPayments** 并填写设置。
4. 在 Docker 环境中，可能需要重启容器或运行 `php artisan octane:reload` 才能使更改生效。

## 配置

| 设置项 | 说明 |
|---|---|
| API Key（API 密钥） | 必填。在 NOWPayments 控制台的 **Store Settings → API keys（商店设置 → API 密钥）** 下创建。 |
| IPN Secret（IPN 密钥） | 必填。在 **Store Settings → Instant payment notifications（商店设置 → 即时付款通知）** 下创建。 |
| Price Currency（计价币种） | 发票金额使用的币种代码，默认值为 `usd`。 |
| Exchange Rate（汇率） | 计价币种每 1 单位对应的网站币种金额，默认值为 `1`。例如，若网站币种为 CNY、计价币种为 USD，可填写 `7.2`。 |
| Fixed Pay Currency（固定付款币种） | 填写 `usdttrc20` 等币种代码以指定付款币种。留空则由客户选择。 |
| Fee Paid By User（由用户支付手续费） | 设置为 `1` 时由客户承担手续费，默认值为 `0`。 |
| Accept "confirmed"（接受 confirmed 状态） | 设置为 `1` 时也将 `confirmed` 视为付款完成。默认只接受 `finished`。 |
| Sandbox Mode（沙盒模式） | 设置为 `1` 时使用沙盒 API；必须提供沙盒 API 密钥。默认值为 `0`。 |

付款金额的计算方式为：将 XBoard 的 `total_amount`（以分为单位）除以 `100 × Exchange Rate`。创建发票时，XBoard 会自动提供 IPN 通知 URL，因此无需在 NOWPayments 控制台中另行登记。该通知 URL 必须能够从互联网访问。

## 付款处理与注意事项

客户在 NOWPayments 托管发票页面付款。XBoard 会验证回调签名，并且仅在付款状态符合设置、订单号和付款 ID 均有效时完成订单。`waiting`、`confirming`、`partially_paid`、`failed` 和 `expired` 等状态不会自动完成订单。部分付款请直接在 NOWPayments 控制台中查看。

生产环境请使用 HTTPS，并切勿泄露 API Key 或 IPN Secret。建议先在沙盒模式下测试集成，再开始接收真实付款。

## 许可证与免责声明

本插件基于 MIT 许可证发布。本插件并非由 XBoard 或 NOWPayments 官方提供或认可。完整许可条款请参阅 [`LICENSE`](LICENSE)。
