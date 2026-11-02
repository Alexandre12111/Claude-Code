#!/bin/sh
# Produit dualtech.zip, dualtech-studio.zip et dualtech-installateur.zip (à envoyer dans WordPress).
cd "$(dirname "$0")/.." || exit 1
for d in dualtech dualtech-studio dualtech-installateur; do
  rm -f "$d.zip"
  zip -qr -X "$d.zip" "$d" -x '*.DS_Store' '*/__pycache__/*'
  echo "$d.zip : $(du -h "$d.zip" | cut -f1)"
done
