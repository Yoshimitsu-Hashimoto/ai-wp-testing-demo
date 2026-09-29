import { defineConfig, devices } from '@playwright/test';

// サイトのURL。ポートは .env の WP_PORT に合わせる。
const wpPort = process.env.WP_PORT ?? '8090';

export default defineConfig({
  testDir: './tests/e2e',
  // 確認用メールボックスを共有するため、テストは1つずつ順番に実行する。
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
