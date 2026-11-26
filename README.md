💎 XBoard NOWPayments

<p align="center">
  <strong>Cryptocurrency Payment Gateway for XBoard</strong>
  <br />
  Accept crypto payments seamlessly with NOWPayments integration.
</p>
<p align="center">
  <img src="https://img.shields.io/badge/Platform-XBoard-blue?style=for-the-badge" />
  <img src="https://img.shields.io/badge/Payment-NOWPayments-00A86B?style=for-the-badge" />
  <img src="https://img.shields.io/badge/Language-PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" />
</p>

⸻

🚀 Overview

XBoard NOWPayments is an independent payment gateway plugin that integrates NOWPayments into XBoard.

It allows your platform to accept cryptocurrency payments through automatically generated invoices and secure IPN (Instant Payment Notification) verification.

✨ Features

<table>
  <tr>
    <td width="50%">
      <h3>💰 Crypto Payments</h3>
      Accept cryptocurrencies supported by NOWPayments.
    </td>
    <td width="50%">
      <h3>🧾 Automatic Invoices</h3>
      Generate payment invoices through the NOWPayments API.
    </td>
  </tr>
  <tr>
    <td>
      <h3>🔔 IPN Notifications</h3>
      Automatically receive payment notifications.
    </td>
    <td>
      <h3>🔐 Signature Verification</h3>
      Verify IPN signatures using HMAC-SHA512.
    </td>
  </tr>
  <tr>
    <td>
      <h3>🧪 Sandbox Mode</h3>
      Test payment integration before going live.
    </td>
    <td>
      <h3>⚙️ Flexible Configuration</h3>
      Configure exchange rates, payment currencies and fees.
    </td>
  </tr>
</table>

📋 Requirements

Requirement	Description
Platform	XBoard
Language	PHP
Payment Provider	NOWPayments
API Credentials	API Key and IPN Secret

📦 Installation

1. Download or clone this repository.
2. Place the plugin in your XBoard plugins directory.
3. Enable the plugin through the XBoard administration panel.
4. Configure your NOWPayments credentials.
5. Start accepting cryptocurrency payments!

Installation paths and activation procedures may vary depending on your XBoard version.

⚙️ Configuration

Configure the following options through the XBoard administration panel.

Setting	Description
API Key	NOWPayments API authentication key
IPN Secret	IPN signature verification secret
Sandbox Mode	Enable sandbox API environment
Exchange Rate	Payment amount conversion rate
Payment Currency	Optional preferred cryptocurrency
Fee Paid by User	Let customers pay transaction fees
Accept Confirmed	Allow confirmed payment status

🌐 API Endpoints

Environment	Endpoint
Production	https://api.nowpayments.io/v1
Sandbox	https://api-sandbox.nowpayments.io/v1

🔄 Payment Workflow

<p align="center">
  <strong>How payment processing works</strong>
</p>
       Customer
           |
           v
      XBoard Checkout
           |
           v
      pay($order)
           |
           v
    NOWPayments API
           |
           v
      Invoice URL
           |
           v
    Customer Payment
           |
           v
      IPN Callback
           |
           v
     notify($params)
           |
           v
   Signature Verification
           |
           v
   Payment Status Check
           |
           v
    XBoard Order Update

🧩 Technical Details

The plugin implements XBoard’s PaymentInterface and extends AbstractPlugin.

<details>
<summary><strong>📂 Plugin.php — Method Overview</strong></summary>

Method	Description
boot()	Registers NOWPayments as an available payment method
form()	Defines administrator configuration fields
pay($order)	Creates payment invoices
notify($params)	Verifies and processes IPN notifications
cfg()	Retrieves plugin configuration
apiBase()	Selects the API environment
isTruthy()	Converts configuration values into boolean values
sortRecursive()	Recursively sorts array keys for signature generation

</details>
<details>
<summary><strong>💳 Payment Creation — pay($order)</strong></summary>

The payment creation process:

1. Validates the API Key.
2. Calculates the payment amount using the configured exchange rate.
3. Builds the invoice request payload.
4. Sends a POST request to the NOWPayments /invoice endpoint.
5. Validates the API response.
6. Returns the invoice URL to XBoard.

Important payload fields:

* price_amount
* price_currency
* order_id
* ipn_callback_url
* success_url
* cancel_url

</details>
<details>
<summary><strong>🔐 IPN Verification — notify($params)</strong></summary>

The notification handler performs the following checks:

1. Checks the IPN Secret and signature header.
2. Reads the original JSON request body.
3. Sorts JSON keys recursively.
4. Generates an HMAC-SHA512 signature.
5. Compares signatures using hash_equals().
6. Validates the payment status.
7. Extracts the order number and payment ID.

By default, only finished payments are accepted.

The accept_confirmed option also allows confirmed payments.

</details>

🛡️ Security

* HMAC-SHA512 signature verification.
* Timing-safe comparison with hash_equals().
* Payment status validation.
* API credential validation.
* Error handling and logging.

Important: Never expose your API Key or IPN Secret. Always use HTTPS and test with sandbox mode before production deployment.

🗺️ Roadmap

* [x]	NOWPayments API integration
* [x]	Invoice creation
* [x]	IPN signature verification
* [x]	Sandbox support
* [ ]	Additional testing and improvements

⚠️ Disclaimer

This is an independent community-developed plugin.

It is not officially affiliated with, maintained by, or endorsed by XBoard or NOWPayments.

Use this software at your own risk.

🤝 Support & Contributions

Found a bug or have a suggestion?

* 🐛 Open an Issue.
* 🔧 Submit a Pull Request.

If you find this project useful, consider giving it a ⭐ Star!

<p align="center">
  <strong>⭐ Enjoying this plugin? Give it a star!</strong>
  <br />
  It helps other XBoard users discover this project.
</p>

⸻

📄 License

Distributed under the MIT License.

See LICENSE for more information.

<p align="center">
  Made with ❤️ for the XBoard community
</p>
