#!/usr/bin/env bash
# Builds dist/w-system-eu-vat-check.zip. Uses `zip` if available, else Python's zipfile.
set -euo pipefail
cd "$(dirname "$0")"
SLUG=w-system-eu-vat-check

if command -v zip >/dev/null 2>&1; then
  ZIPPER=zip
elif command -v python3 >/dev/null 2>&1; then
  ZIPPER=python
else
  echo "error: neither 'zip' nor 'python3' is available; dist/ left untouched" >&2
  exit 1
fi

# Build in a temp dir first so dist/ is only replaced on success.
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
mkdir -p "$TMP/$SLUG"
cp -r w-system-eu-vat-check.php uninstall.php readme.txt LICENSE.txt includes "$TMP/$SLUG/"

if [ "$ZIPPER" = zip ]; then
  (cd "$TMP" && zip -qr "$SLUG.zip" "$SLUG")
else
  (cd "$TMP" && python3 - "$SLUG" <<'PY'
import os, sys, zipfile
slug = sys.argv[1]
with zipfile.ZipFile(slug + ".zip", "w", zipfile.ZIP_DEFLATED) as z:
    for root, dirs, files in os.walk(slug):
        dirs.sort()
        z.write(root, root + "/")
        for f in sorted(files):
            z.write(os.path.join(root, f))
PY
  )
fi

rm -rf dist && mkdir -p dist
mv "$TMP/$SLUG" "$TMP/$SLUG.zip" dist/
echo "dist/$SLUG.zip"
