#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"
rm -rf dist && mkdir -p dist/w-system-vies-check
cp -r w-system-vies-check.php uninstall.php readme.txt LICENSE.txt includes dist/w-system-vies-check/
(cd dist && zip -qr w-system-vies-check.zip w-system-vies-check)
echo dist/w-system-vies-check.zip
