#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"
rm -rf dist && mkdir -p dist/w-system-eu-vat-check
cp -r w-system-eu-vat-check.php uninstall.php readme.txt LICENSE.txt includes dist/w-system-eu-vat-check/
(cd dist && zip -qr w-system-eu-vat-check.zip w-system-eu-vat-check)
echo dist/w-system-eu-vat-check.zip
