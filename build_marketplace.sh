#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
DIST_DIR="$ROOT_DIR/dist"
PACKAGE_NAME=".last_version"
ARCHIVE_NAME=".last_version.zip"
PACKAGE_DIR="$DIST_DIR/$PACKAGE_NAME"
ARCHIVE_PATH="$DIST_DIR/$ARCHIVE_NAME"

export COPYFILE_DISABLE=1

rm -rf "$PACKAGE_DIR" "$ARCHIVE_PATH"
mkdir -p "$PACKAGE_DIR"

cp -R "$ROOT_DIR/admin" "$PACKAGE_DIR/"
cp -R "$ROOT_DIR/install" "$PACKAGE_DIR/"
cp -R "$ROOT_DIR/lang" "$PACKAGE_DIR/"
cp -R "$ROOT_DIR/lib" "$PACKAGE_DIR/"
cp "$ROOT_DIR/include.php" "$PACKAGE_DIR/"

(
    cd "$DIST_DIR"
    zip -qr "$(basename "$ARCHIVE_PATH")" "$(basename "$PACKAGE_DIR")"
)

echo "Marketplace archive created: $ARCHIVE_PATH"
