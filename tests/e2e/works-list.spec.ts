import { test, expect, type Page } from '@playwright/test';

// 制作実績一覧（/works/）。期待値は docs/test-data.md のテストデータに基づく。

const worksByIndustry = {
  飲食: ['和食処 やまの葉 様', 'カフェ こもれび 様', 'ベーカリー麦の音 様'],
  製造: ['高橋精密工業株式会社 様', '株式会社青木製作所 様', '東和木工株式会社 様'],
  サービス: ['はるかサービス 様', 'みどり不動産 様', 'さくら学習室 様'],
} as const;

// 公開日の新しい順
const allPublishedWorks = [
  '和食処 やまの葉 様',
  '高橋精密工業株式会社 様',
  'はるかサービス 様',
  'カフェ こもれび 様',
  '株式会社青木製作所 様',
  'みどり不動産 様',
  'ベーカリー麦の音 様',
  '東和木工株式会社 様',
  'さくら学習室 様',
];

const draftWork = '喫茶 ひだまり 様';

const filterNav = (page: Page) => page.getByRole('navigation', { name: '業種で絞り込む' });
const workTitles = (page: Page) => page.locator('.works-grid .work-title');
const resultCount = (page: Page) => page.locator('.result-count');

test.describe('制作実績一覧', () => {
  test('TC-01 [SPEC-01][SPEC-08] 公開済みの9件だけが公開日の新しい順に表示され、下書きは表示されない', async ({ page }) => {
    await page.goto('/works/');

    await expect(workTitles(page)).toHaveText(allPublishedWorks);
    await expect(page.getByText(draftWork)).toHaveCount(0);
    await expect(resultCount(page)).toHaveText('9件の実績');
  });

  test('TC-02 [SPEC-09] 最初は「すべて」だけが選択状態になっている', async ({ page }) => {
    await page.goto('/works/');

    await expect(filterNav(page).getByRole('link')).toHaveText(['すべて', '飲食', '製造', 'サービス', '医療']);
    await expect(filterNav(page).locator('[aria-current="page"]')).toHaveText(['すべて']);
  });

  for (const [index, [industry, titles]] of Object.entries(worksByIndustry).entries()) {
    test(`TC-03-${index + 1} [SPEC-02][SPEC-08][SPEC-09] 業種「${industry}」を選ぶと、その業種の3件だけが表示される`, async ({ page }) => {
      await page.goto('/works/');
      await filterNav(page).getByRole('link', { name: industry, exact: true }).click();

      await expect(workTitles(page)).toHaveText([...titles]);
      await expect(page.locator('.works-grid .work-category')).toHaveText([industry, industry, industry]);
      await expect(resultCount(page)).toHaveText('3件の実績');
      await expect(filterNav(page).locator('[aria-current="page"]')).toHaveText([industry]);
    });
  }

  test('TC-04 [SPEC-03] 業種で絞り込んだあと「すべて」を選ぶと、絞り込みが解除される', async ({ page }) => {
    await page.goto('/works/');
    await filterNav(page).getByRole('link', { name: '飲食', exact: true }).click();
    await expect(resultCount(page)).toHaveText('3件の実績');

    await filterNav(page).getByRole('link', { name: 'すべて', exact: true }).click();

    await expect(workTitles(page)).toHaveText(allPublishedWorks);
    await expect(resultCount(page)).toHaveText('9件の実績');
    await expect(filterNav(page).locator('[aria-current="page"]')).toHaveText(['すべて']);
  });

  test('TC-05 [SPEC-07][SPEC-08] 実績が0件の業種「医療」を選ぶと、該当なしのメッセージが表示される', async ({ page }) => {
    await page.goto('/works/');
    await filterNav(page).getByRole('link', { name: '医療', exact: true }).click();

    await expect(workTitles(page)).toHaveCount(0);
    await expect(page.getByText('該当する実績はありません。')).toBeVisible();
    await expect(resultCount(page)).toHaveText('0件の実績');
    await expect(filterNav(page).locator('[aria-current="page"]')).toHaveText(['医療']);
  });

  test('TC-06 [SPEC-07][SPEC-09] 存在しない業種をURLで指定すると、該当0件になり、どのボタンも選択状態にならない', async ({ page }) => {
    await page.goto('/works/?industry=not-exist');

    await expect(workTitles(page)).toHaveCount(0);
    await expect(page.getByText('該当する実績はありません。')).toBeVisible();
    await expect(filterNav(page).locator('[aria-current="page"]')).toHaveCount(0);
  });

  test('TC-07 [SPEC-02] 業種のスラッグは大文字と小文字を区別しない', async ({ page }) => {
    await page.goto('/works/?industry=FOOD');

    await expect(workTitles(page)).toHaveText([...worksByIndustry['飲食']]);
    await expect(filterNav(page).locator('[aria-current="page"]')).toHaveText(['飲食']);
  });

  test('TC-08 [SPEC-03] 業種の指定が空の場合は「すべて」と同じ表示になる', async ({ page }) => {
    await page.goto('/works/?industry=');

    await expect(workTitles(page)).toHaveText(allPublishedWorks);
    await expect(filterNav(page).locator('[aria-current="page"]')).toHaveText(['すべて']);
  });

  test('TC-09 実績のカードを選ぶと、その実績の詳細ページに移動する', async ({ page }) => {
    await page.goto('/works/');
    await page.locator('.work-card', { hasText: '和食処 やまの葉 様' }).click();

    await expect(page).toHaveURL(/\/works\/yamanoha\/$/);
    await expect(page.locator('.detail-top h2')).toHaveText('和食処 やまの葉 様');
  });
});
