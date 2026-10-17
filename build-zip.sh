#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"
rm -rf dist && mkdir -p dist/eu-vat-validator-wsystem
cp -r eu-vat-validator-wsystem.php uninstall.php readme.txt LICENSE.txt includes dist/eu-vat-validator-wsystem/
(cd dist && zip -qr eu-vat-validator-wsystem.zip eu-vat-validator-wsystem)
echo dist/eu-vat-validator-wsystem.zip
