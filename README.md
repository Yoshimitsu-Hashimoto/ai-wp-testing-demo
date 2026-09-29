# AIと作るWordPressサイト — 構築からテスト・修正まで

90分の実演講座「AIと作るWordPressサイト — Dockerで構築し、テスト・バグ修正まで体験する90分」で使うデモ用リポジトリです。

小さな「制作会社の実績紹介サイト」をDocker上のWordPressで構築し、次の流れをAI（Claude Code）とPlaywrightで実演します。

**仕様書 → 構築 → テスト計画 → テスト → バグ管理表 → GitHub Issue → 修正 → 再テスト**

## 題材サイトの機能

| 対象 | 内容 |
|---|---|
| 実績一覧・詳細 | カスタム投稿タイプ「制作実績」 |
| 実績の追加情報 | カスタムフィールド「顧客名・制作年・担当範囲」 |
| 絞り込み | カスタムタクソノミー「業種」で実績を絞り込む |
| お問い合わせ | 名前・メール・本文・同意チェック・送信完了表示。送信内容はローカルのメール確認環境に届く |

## 資料

| 資料 | 内容 |
|---|---|
| [docs/spec.md](docs/spec.md) | サイト仕様書。仕様IDと受け入れ条件 |
| [docs/test-data.md](docs/test-data.md) | テストデータと期待する件数 |
| [docs/spec-ambiguous-example.md](docs/spec-ambiguous-example.md) | 曖昧な仕様と、判断できる仕様の比較 |
| [docs/bug-list.md](docs/bug-list.md) | バグ管理表 |

## ローカル環境の起動

Docker Desktop（Docker Compose v2）が必要です。

```bash
./scripts/setup.sh
```

初回はWordPressのインストール、初期設定（サイト名・日本語化・タイムゾーン・パーマリンク・テーマ `hinata` の有効化）、テストデータの投入まで行います。2回目以降も同じコマンドで起動でき、初期設定は揃った状態に保たれます。

初回は日本語パックとDockerイメージをダウンロードするため、ネットワーク接続が必要です。講座で使う場合は、事前に一度実行しておいてください。

| 用途 | URL |
|---|---|
| サイト | http://localhost:8090/ |
| 管理画面 | http://localhost:8090/wp-admin/ |
| 確認用メールボックス（Mailpit） | http://localhost:8091/ |

管理画面のログイン情報は `.env.example` にあります。ローカルの講座用環境でだけ使うテスト用の値です。ポートや管理者情報を変える場合は、`.env.example` を `.env` にコピーして編集します。ポートを変えた場合はサイトのURLも追従します。管理者情報はインストール時にだけ使われるため、変更後は `./scripts/reset.sh` で作り直してください。

サイトとMailpitは、このPC（localhost）からだけ開けます。

WordPressから送信したメールは外部には送られず、すべてMailpitに届きます。

| 操作 | コマンド |
|---|---|
| 停止 | `docker compose stop` |
| テストデータを入れ直し、確認用メールボックスを空にする | `./scripts/seed.sh` |
| 今の状態を保存する／保存した状態に戻す（データベースとアップロード画像のみ。テーマのコードはgitで切り替える） | `./scripts/snapshot.sh save <名前>` ／ `./scripts/snapshot.sh restore <名前>` |
| 環境をすべて削除し、テストデータを入れた状態で作り直す（ネットワーク接続が必要） | `./scripts/reset.sh` |
| WP-CLIを使う | `docker compose run --rm wpcli wp <コマンド>` |

### 使用しているイメージ

| サービス | イメージ |
|---|---|
| WordPress | `wordpress:7.1.2-php8.3-apache` |
| WP-CLI | `wordpress:cli-2.12.0-php8.3` |
| データベース | `mariadb:11.4.13` |
| メール確認 | `axllent/mailpit:v1.31.3` |

## テスト（Playwright）

Node.js 20.12以上が必要です。初回だけ次を実行します。

```bash
npm install
npx playwright install chromium
```

| 操作 | コマンド |
|---|---|
| テストを実行する | `npm test` |
| 結果のレポートを開く（失敗時のスクリーンショット・トレースを含む） | `npm run test:report` |

テストは `tests/e2e/` に置きます。テストの前に `./scripts/seed.sh` でテストデータと確認用メールボックスを初期状態に戻しておきます。

- ポートは `.env` の `WP_PORT`・`MAILPIT_PORT` を読み込みます（`.env` がなければ 8090・8091）。
- テストは並列にせず、1つずつ実行します。お問い合わせのテストが確認用メールボックスを共有しているためです（`playwright.config.ts` の `workers: 1`）。

Claude Codeからブラウザを操作するための Playwright MCP の設定は `.mcp.json` にあります。Claude Codeでこのリポジトリを開き、MCPサーバーの利用を許可すると使えます。Playwright MCP は、このPCにインストールされている Google Chrome を使います。ログイン状態などは保存されません（`--isolated`）。

## WordPressテーマ（theme/hinata）

| ファイル | 役割 |
|---|---|
| `inc/post-types.php` | カスタム投稿タイプ「制作実績」（`works`）とカスタムタクソノミー「業種」（`industry`） |
| `inc/works-meta.php` | カスタムフィールド「顧客名・制作年・担当範囲」と管理画面の入力欄 |
| `inc/works-query.php` | 実績一覧の業種による絞り込み（`/works/?industry=<業種のスラッグ>`） |
| `inc/contact-form.php` | お問い合わせフォームの入力チェックとメール送信 |
| `archive-works.php` / `single-works.php` | 実績一覧・詳細 |
| `page-contact.php` / `page-thanks.php` | お問い合わせ・送信完了（固定ページ `contact` / `thanks`） |
| `page-company.php` / `page-privacy.php` | 会社案内・プライバシーポリシー（固定ページ `company` / `privacy`） |

固定ページはスラッグで対応するテンプレートが使われます。フォームやカスタムフィールドにプラグインは使っていません。固定ページ・業種・制作実績はテストデータとして投入します（[docs/test-data.md](docs/test-data.md)）。

お問い合わせフォームの入力チェックはサーバー側で行い、エラーを画面に表示します（ブラウザ標準の入力チェックは `novalidate` で無効にしています）。

実績一覧の絞り込みでは、存在しない業種や業種として使えない値を指定すると該当0件になります。

## ブランチ

| ブランチ | 状態 |
|---|---|
| `main` | 最新の教材（`fixed` と同じ正しい実装） |
| `start` | 実績一覧の業種による絞り込みを実装する前の状態 |
| `buggy` | 実演用のバグが入った状態 |
| `fixed` | バグ修正済み |
| `reference` | 解答例（講座後の参考用） |

テーマはDockerのコンテナにそのままマウントされているため、ブランチを切り替えるとすぐにサイトに反映されます。データベースはブランチと関係なく共通です。

```bash
git switch buggy
```

## ディレクトリ構成

| パス | 内容 |
|---|---|
| `static-site/` | WordPress化する前の静的HTML/CSS（デザインの元） |
| `theme/hinata/` | WordPressテーマ |
| `compose.yaml` | Docker環境の定義 |
| `tests/e2e/` | Playwrightのテスト |
| `playwright.config.ts` / `package.json` | Playwrightの設定 |
| `.mcp.json` | Claude Code用の Playwright MCP の設定 |
| `docker/mu-plugins/` | ローカル環境用の設定（メールをMailpitへ送る） |
| `scripts/` | 環境の起動・初期化、テストデータ投入、スナップショットのスクリプト |
| `docs/` | 仕様書・テストデータ・バグ管理表など |
| `.github/ISSUE_TEMPLATE/` | バグ報告用のIssueテンプレート |

