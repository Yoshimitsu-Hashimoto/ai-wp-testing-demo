#!/usr/bin/env bash
# 講座用の固定テストデータを投入し、確認用メールボックスを空にする。
# 既存の投稿・固定ページ・制作実績・メディア・業種は削除される。
set -euo pipefail
cd "$(dirname "$0")/.."

if [ -f .env ]; then
  set -a; source .env; set +a
fi
MAILPIT_PORT="${MAILPIT_PORT:-8091}"

docker compose run --rm -T -v "$PWD/scripts:/scripts:ro" wpcli wp eval-file /scripts/seed.php

if ! curl -fsS -X DELETE "http://localhost:${MAILPIT_PORT}/api/v1/messages" >/dev/null; then
  echo "確認用メールボックスを空にできませんでした。Mailpitが起動しているか確認してください。" >&2
  exit 1
fi
echo "確認用メールボックスを空にしました。"
