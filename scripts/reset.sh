#!/usr/bin/env bash
# データベースとWordPressのファイルを削除し、初期状態から作り直す。
set -euo pipefail
cd "$(dirname "$0")/.."

docker compose --profile cli down -v
exec ./scripts/setup.sh
