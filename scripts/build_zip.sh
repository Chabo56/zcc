#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST_DIR="$ROOT_DIR/dist"
ZIP_NAME="zcc.zip"

mkdir -p "$DIST_DIR"

cd "$ROOT_DIR"
rm -f "$DIST_DIR/$ZIP_NAME"

zip -r "$DIST_DIR/$ZIP_NAME" \
  app config modules public resources storage bootstrap.php m.php modules.php README.md \
  -x "**/.git*" "**/dist/*" "**/storage/*.log"

echo "Created $DIST_DIR/$ZIP_NAME"
