# W System EU VAT Check for WooCommerce (WordPress plugin)


**Install:** [W System EU VAT Check for WooCommerce on WordPress.org](https://wordpress.org/plugins/w-system-eu-vat-check/), or search "W System EU VAT Check" under Plugins, Add New.

Free WordPress plugin: validates EU VAT numbers at WooCommerce checkout via VIES (W System API, free 20/day, optional paid key),
applies B2B reverse charge for valid cross-border EU numbers, and stores the VIES consultation number in order meta
(`_wsevv_vat_number`, `_wsevv_status`, `_wsevv_consultation_number`, `_wsevv_company_name`, `_wsevv_checked_at`, `_wsevv_reverse_charge`).

See `readme.txt` for the wordpress.org description. GPLv2 or later.

Build the wordpress.org ZIP: `./build-zip.sh` → `dist/w-system-eu-vat-check.zip`.
Coding standards: `phpcs --standard=WordPress .` (clean).

Guide and install steps: https://wsystem.dev/woocommerce-eu-vat-number-validation
Download: https://github.com/wsystemdev/woo-eu-vat-validator/releases/latest/download/w-system-eu-vat-check.zip
