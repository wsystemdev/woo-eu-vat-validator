=== EU VAT Number Validator by W System ===
Contributors: wsystem
Tags: vat, vies, eu vat, reverse charge, b2b
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Validate EU VAT numbers at WooCommerce checkout against VIES, apply B2B reverse charge and keep the VIES consultation number on each order.

== Description ==

EU VAT Number Validator by W System adds an "EU VAT number" field to the WooCommerce checkout and checks it against the European Commission's VIES service.

* **Live VIES validation** for all 27 EU member states and Northern Ireland (XI). Greek numbers may be entered as GR or EL.
* **B2B reverse charge:** when the VAT number is valid and from an EU country other than your store base country, VAT is removed from the order (zero-rated intra-community supply).
* **Audit proof:** the VIES consultation number, status, registered company name and check date are stored in the order meta and an order note, and shown in the admin order screen and emails.
* **Country check:** the VAT number prefix must match the billing country.
* **Configurable** behaviour for invalid numbers and for when VIES is down.
* **Free:** 20 checks per day without any account; results are cached for 24 hours. An optional paid API key raises the volume.
* HPOS (custom order tables) compatible.

Settings: WooCommerce > Settings > Tax > EU VAT validator.

This plugin is not legal or tax advice. Check the VAT rules that apply to your business.

== External services ==

This plugin connects to the W System EU business data API (https://api.wsystem.dev), operated by W System Hungary Kft., to validate VAT numbers against the official EU VIES service.

* What is sent and when: the VAT number entered at checkout and, if configured, your own store VAT number (requester), each time the checkout totals are refreshed or an order is placed (cached for 24 hours per number). If you set an API key it is sent as a header. No other customer data is sent.
* Terms of service and imprint: https://wsystem.dev/#imprint
* Privacy policy: https://wsystem.dev/privacy

VIES itself is a European Commission service: https://ec.europa.eu/taxation_customs/vies/

== Installation ==

1. Install and activate the plugin (WooCommerce must be active).
2. Go to WooCommerce > Settings > Tax > EU VAT validator.
3. Enter your own EU VAT number to receive consultation numbers. Optionally add an API key.

== Frequently Asked Questions ==

= Do I need an account? =

No. Without an API key the free tier allows 20 checks per day per site. For more volume add a paid API key.

= What happens when VIES is down? =

You choose: charge VAT, apply reverse charge and verify later, or block checkout.

= Does it work with the block-based Checkout? =

Version 1.0 supports the classic (shortcode) checkout. Block checkout support is planned.

== Screenshots ==

1. Settings screen.
2. VAT number field at checkout with reverse charge applied.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
