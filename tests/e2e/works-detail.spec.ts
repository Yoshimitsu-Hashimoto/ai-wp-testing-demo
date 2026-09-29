import { test, expect, type Page } from '@playwright/test';

// 制作実績の詳細（/works/<スラッグ>/）とトップページ。期待値は docs/test-data.md に基づく。

const metaHeadings = (page: Page) => page.locator('.meta-table th');
const metaValues = (page: Page) => page.locator('.meta-table td');

test.describe('制作実績の詳細', () => {
  test('TC-10 [SPEC-10] 登録された顧客名・制作年・担当範囲が表で表示され、制作年に「年」が付く', async ({ page }) => {
    await page.goto('/works/yamanoha/');

    await expect(metaHeadings(page)).toHaveText(['顧客名', '制作年', '担当範囲']);
    await expect(metaValues(page)).toHaveText(['和食処 やまの葉', '2026年', '企画・デザイン・WordPress構築']);
  });

  test('TC-11 [SPEC-04] 制作年が未入力の実績では、「制作年」の行を表示しない', async ({ page }) => {
    await page.goto('/works/sakura-gakushu/');

    await expect(metaHeadings(page)).toHaveText(['顧客名', '担当範囲']);
    await expect(metaValues(page)).toHaveText(['さくら学習室', 'デザイン・WordPress構築']);
    await expect(page.locator('.meta-table')).not.toContainText('制作年');
  });

  test('TC-12 [SPEC-01] 下書きの実績の詳細URLを開くと、ページが見つからない', async ({ page }) => {
    const response = await page.goto('/works/hidamari-draft/');

    expect(response?.status()).toBe(404);
    await expect(page.getByText('喫茶 ひだまり 様')).toHaveCount(0);
  });

  test('TC-13 「制作実績一覧に戻る」で一覧（すべて）に戻る', async ({ page }) => {
    await page.goto('/works/yamanoha/');
    await page.getByRole('link', { name: /制作実績一覧に戻る/ }).click();

    await expect(page).toHaveURL(/\/works\/$/);
    await expect(page.locator('.result-count')).toHaveText('9件の実績');
  });
});

test.describe('トップページ', () => {
  test('TC-14 [SPEC-11][SPEC-01] 公開済みの実績が公開日の新しい順に3件表示され、下書きは含まれない', async ({ page }) => {
    await page.goto('/');

    await expect(page.locator('.works-grid .work-title')).toHaveText([
      '和食処 やまの葉 様',
      '高橋精密工業株式会社 様',
      'はるかサービス 様',
    ]);
    await expect(page.getByText('喫茶 ひだまり 様')).toHaveCount(0);
  });
});
