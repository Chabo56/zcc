#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST_DIR="$ROOT_DIR/dist/modules"

mkdir -p "$DIST_DIR"

for module_dir in "$ROOT_DIR"/modules/*; do
  if [[ ! -d "$module_dir" ]]; then
    continue
  fi
  if [[ ! -f "$module_dir/module.json" ]]; then
    continue
  fi
  module_name="$(basename "$module_dir")"
  zip_path="$DIST_DIR/${module_name}.zip"
  rm -f "$zip_path"
  (cd "$module_dir" && zip -r "$zip_path" .)
  echo "Created $zip_path"
 done
