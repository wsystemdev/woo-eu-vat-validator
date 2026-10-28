# Block checkout end-to-end test

`store_api_test.py` drives the WooCommerce Store API (the API the block Checkout uses) against a local
WordPress + WooCommerce site with this plugin active, and checks five cases: valid cross-border VAT
number (tax 0, order placed, VIES meta stored), invalid number (refused), country mismatch (refused),
no number (normal VAT), domestic number (no reverse charge). It calls the live W System API.

Setup used (PHP 8.x, SQLite, no MySQL): WordPress latest + sqlite-database-integration drop-in +
WooCommerce latest, base country HU, taxes on (DE 19 %, HU 27 %), one virtual product id 10 priced
100, cheque gateway on, `php -S 127.0.0.1:8099`. Then `python3 tests/store_api_test.py`.
