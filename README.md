# W System EU VAT Check for WooCommerce (WordPress plugin)

Free WordPress plugin: validates EU VAT numbers at WooCommerce checkout via VIES (W System API, free 20/day, optional paid key),
applies B2B reverse charge for valid cross-border EU numbers, and stores the VIES consultation number in order meta
(`_wsevv_vat_number`, `_wsevv_status`, `_wsevv_consultation_number`, `_wsevv_company_name`, `_wsevv_checked_at`, `_wsevv_reverse_charge`).

See `readme.txt` for the wordpress.org description. GPLv2 or later.

Build the wordpress.org ZIP: `./build-zip.sh` → `dist/w-system-eu-vat-check.zip`.
Coding standards: `phpcs --standard=WordPress .` (clean).
