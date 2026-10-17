=== W System VIES Check ===
Contributors: wsystemdev
Tags: vat, vies, eu vat, reverse charge, b2b
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Check EU VAT numbers in VIES at WooCommerce checkout, apply B2B reverse charge and keep the VIES consultation number on each order.

== Description ==

W System VIES Check adds an "EU VAT number" field to the WooCommerce checkout and checks it against the European Commission's VIES service through the W System API.

* **Live VIES validation** for all 27 EU member states and Northern Ireland (XI). Greek numbers may be entered as GR or EL.
* **B2B reverse charge:** when the VAT number is valid and from an EU country other than your store base country, VAT is removed from the order (zero-rated intra-community supply).
* **Audit proof:** the VIES consultation number, status, registered company name and check date are stored in the order meta and an order note, and shown in the admin order screen and emails.
* **Country check:** the VAT number prefix must match the billing country.
* **Configurable** behaviour for invalid numbers and for when VIES is down.
* **No locked features:** every feature works without an account or API key. The free tier of the external W System API allows 20 checks per day per server IP address, and results are cached for 24 hours per number. An optional API key only raises that external service quota.
* HPOS (custom order tables) compatible.

Settings: WooCommerce > Settings > Tax > EU VAT validator.

This plugin is not legal or tax advice. Check the VAT rules that apply to your business. It is not affiliated with or endorsed by the European Commission or WooCommerce.

== External services ==

This plugin connects to the W System API (https://api.wsystem.dev), operated by W System Hungary Kft., Budapest, Hungary. The API looks up VAT numbers in the European Commission VIES service. Without this service the plugin cannot validate VAT numbers.

* **What is sent:** the EU VAT number the customer types into the checkout field; your own store VAT number (as "requester") if you entered one in the settings, so that VIES returns a consultation number; and the API key, as the X-API-Key header, only if you entered one. As with any HTTP request, the API also sees your server's IP address, which it uses to count the free daily quota. No names, addresses, emails, order contents or other customer data are sent.
* **When it is sent:** only when a VAT number is entered at checkout: each time the checkout totals are refreshed with a VAT number in the field, and when the order is placed. Results are cached on your site for 24 hours per VAT number, so repeated checks of the same number are not sent again. Nothing is sent from the admin area, on activation, or on other pages.
* **Endpoint:** https://api.wsystem.dev/verify
* **Terms of service:** https://wsystem.dev/terms
* **Privacy policy:** https://wsystem.dev/privacy

The API forwards the VAT number (and requester VAT number) to the European Commission VIES service: https://ec.europa.eu/taxation_customs/vies/.

== Installation ==

1. Install and activate the plugin (WooCommerce must be active).
2. Go to WooCommerce > Settings > Tax > EU VAT validator.
3. Optionally enter your own EU VAT number to receive VIES consultation numbers. No API key is needed.

== Frequently Asked Questions ==

= Do I need an account or API key? =

No. All plugin features work without one. The W System API free tier allows 20 checks per day per server IP address (results are cached for 24 hours). If you need more checks per day, an API key for higher volume can be entered in the settings; see https://wsystem.dev/#pricing.

= What happens when the daily free quota is used up? =

The plugin treats it like VIES being unavailable and follows your "VIES unavailable" setting: charge VAT, apply reverse charge and verify later, or block checkout.

= What happens when VIES is down? =

You choose: charge VAT, apply reverse charge and verify later, or block checkout.

= Does it work with the block-based Checkout? =

Version 1.0 supports the classic (shortcode) checkout. Block checkout support is planned.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
