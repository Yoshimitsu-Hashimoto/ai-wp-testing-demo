#!/usr/bin/env bash
# データベースとアップロード画像を保存・復元する。
#   ./scripts/snapshot.sh save <名前>     snapshots/<名前>/ に保存
#   ./scripts/snapshot.sh restore <名前>  snapshots/<名前>/ から復元
# 保存時と同じポート（WP_PORT）の環境で復元する。
set -euo pipefail
cd "$(dirname "$0")/.."

usage() {
  echo "使い方: $0 save|restore <名前>" >&2
  exit 1
}

[ $# -eq 2 ] || usage
action="$1"
name="$2"
if ! [[ "$name" =~ ^[A-Za-z0-9][A-Za-z0-9._-]*$ ]]; then
  echo "名前は英数字で始め、英数字と . _ - だけを使ってください。" >&2
  exit 1
fi
dir="snapshots/$name"

wpcli() { docker compose run --rm -T "$@"; }

case "$action" in
  save)
    mkdir -p "$dir"
    # 書き出しに失敗しても既存のスナップショットを壊さないよう、一時ファイルに書いてから置き換える
    wpcli wpcli wp db export - > "$dir/db.sql.tmp"
    wpcli --entrypoint tar wpcli -czf - -C /var/www/html/wp-content uploads > "$dir/uploads.tgz.tmp"
    if [ ! -s "$dir/db.sql.tmp" ] || [ ! -s "$dir/uploads.tgz.tmp" ]; then
      rm -f "$dir/db.sql.tmp" "$dir/uploads.tgz.tmp"
      echo "保存に失敗しました。" >&2
      exit 1
    fi
    mv "$dir/db.sql.tmp" "$dir/db.sql"
    mv "$dir/uploads.tgz.tmp" "$dir/uploads.tgz"
    echo "保存しました: $dir"
    ;;
  restore)
    if [ ! -s "$dir/db.sql" ] || [ ! -s "$dir/uploads.tgz" ]; then
      echo "スナップショットが見つからないか、中身が空です: $dir" >&2
      exit 1
    fi
    wpcli wpcli wp db reset --yes
    wpcli wpcli wp db import - < "$dir/db.sql"
    wpcli --entrypoint sh wpcli -c 'rm -rf /var/www/html/wp-content/uploads && tar -xzf - -C /var/www/html/wp-content' < "$dir/uploads.tgz"
    echo "復元しました: $dir"
    ;;
  *)
    usage
    ;;
esac
