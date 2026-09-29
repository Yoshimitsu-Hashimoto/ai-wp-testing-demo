#!/usr/bin/env bash
# 環境を起動し、WordPressを初期設定する。インストール済みならインストールは省略し、初期設定は毎回適用する。
set -euo pipefail
cd "$(dirname "$0")/.."

if [ -f .env ]; then
  set -a; source .env; set +a
fi
WP_PORT="${WP_PORT:-8090}"
MAILPIT_PORT="${MAILPIT_PORT:-8091}"
WP_TITLE="${WP_TITLE:-Works Studio}"
WP_ADMIN_USER="${WP_ADMIN_USER:-admin}"
WP_ADMIN_PASSWORD="${WP_ADMIN_PASSWORD:-admin-password}"
WP_ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@example.test}"
SITE_URL="http://localhost:${WP_PORT}"

wp() { docker compose run --rm -T wpcli wp "$@"; }

docker compose up -d --wait

# WordPress本体のファイルが展開されるまで待つ
ready=false
for _ in $(seq 1 60); do
  if docker compose exec -T wordpress test -f /var/www/html/wp-config.php; then
    ready=true
    break
  fi
  sleep 1
done
if [ "$ready" != true ]; then
  echo "WordPressの起動を確認できませんでした。docker compose logs wordpress を確認してください。" >&2
  exit 1
fi

if wp core is-installed 2>/dev/null; then
  echo "WordPressはインストール済みです。"
else
  wp core install \
    --url="$SITE_URL" \
    --title="$WP_TITLE" \
    --admin_user="$WP_ADMIN_USER" \
    --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" \
    --skip-email
fi

# 初期設定。途中で失敗しても再実行で揃うよう、毎回適用する。
# 日本語パックは wordpress.org から取得するため、初回はネットワーク接続が必要。
if ! wp language core is-installed ja; then
  wp language core install ja
fi
if [ "$(wp option get WPLANG 2>/dev/null || true)" != ja ]; then
  wp site switch-language ja
fi
wp option update timezone_string Asia/Tokyo
wp option update date_format 'Y年n月j日'
# .htaccess は WordPress イメージに同梱のものを使う
wp rewrite structure '/%postname%/'
for plugin in akismet hello; do
  if wp plugin is-installed "$plugin"; then
    wp plugin delete "$plugin"
  fi
done

echo
echo "サイト:       ${SITE_URL}/"
echo "管理画面:     ${SITE_URL}/wp-admin/"
echo "Mailpit:      http://localhost:${MAILPIT_PORT}/"
