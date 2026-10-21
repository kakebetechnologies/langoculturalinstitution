#!/bin/bash
# Convenience script: checks that the website/API (XAMPP) answers, then starts the Expo dev server.
# The PHP site is served by Apache from XAMPP (http://localhost/langoculturalinstitution/), so only Expo needs starting.
# Usage (from the repo root):  ./scripts/dev.sh      Stop with Ctrl+C.
set -e
cd "$(dirname "$0")/.."

SITE="${LCI_SITE:-http://localhost/langoculturalinstitution}"
if curl -fsS "$SITE/api/v1/ping" >/dev/null 2>&1; then
  echo "API is up: $SITE/api/v1"
else
  echo "Cannot reach $SITE/api/v1/ping — start Apache and MySQL in XAMPP, and run: php web/database/install-cli.php"
  echo "(Starting Expo anyway; the app will show its offline state until the API answers.)"
fi

cd app
[ -d node_modules ] || npm install
npx expo start
