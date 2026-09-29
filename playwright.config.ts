import { existsSync } from 'node:fs';
import { defineConfig, devices } from '@playwright/test';

// .env があれば読み込み、Docker環境と同じポート（WP_PORT・MAILPIT_PORT）を使う。
if (existsSync('.env')) {
  process.loadEnvFile('.env');
}

// サイトのURL
const wpPort = process.env.WP_PORT ?? '8090';

export default defineConfig({
  testDir: './tests/e2e',
  // お問い合わせのテストが確認用メールボックスを共有するため、テストは1つずつ順番に実行する。
  fullyParallel: false,
  workers: 1,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL: process.env.BASE_URL ?? `http://localhost:${wpPort}`,
    locale: 'ja-JP',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
