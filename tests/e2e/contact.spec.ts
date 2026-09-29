import { test, expect, type Page } from '@playwright/test';
import { clearMailbox, getMail, listMails, waitForMails } from './helpers/mailpit';

// お問い合わせ（/contact/）。送信内容は確認用メールボックス（Mailpit）で確かめる。

type ContactInput = {
  name: string;
  email: string;
  message: string;
  consent: boolean;
};

const validInput: ContactInput = {
  name: '山田 太郎',
  email: 'yamada@example.test',
  message: 'Webサイトのリニューアルについて相談したいです。\n予算は相談させてください。',
  consent: true,
};

async function fillContactForm(page: Page, input: ContactInput) {
  await page.getByLabel('お名前').fill(input.name);
  await page.getByLabel('メールアドレス').fill(input.email);
  await page.getByLabel('お問い合わせ内容').fill(input.message);
  await page.getByRole('checkbox', { name: /プライバシーポリシー/ }).setChecked(input.consent);
}

async function submit(page: Page) {
  await page.getByRole('button', { name: /送信する/ }).click();
  await page.waitForLoadState('domcontentloaded');
}

/** 送信できなかったことを確かめる：お問い合わせページに留まり、エラーが出て、メールは届かない。 */
async function expectRejected(page: Page, request: Parameters<typeof listMails>[0], messages: string[]) {
  await expect(page).toHaveURL(/\/contact\/$/);
  await expect(page.locator('.form-errors')).toContainText('入力内容に問題があります。');
  await expect(page.locator('.form-errors li')).toHaveText(messages);
  expect(await listMails(request)).toHaveLength(0);
}

test.describe('お問い合わせ', () => {
  test.beforeEach(async ({ page, request }) => {
    await clearMailbox(request);
    await page.goto('/contact/');
  });

  test('TC-15 [SPEC-06] 正しく入力して送信すると送信完了ページに移動し、入力内容のメールが1通届く', async ({ page, request }) => {
    await fillContactForm(page, validInput);
    await submit(page);

    await expect(page).toHaveURL(/\/thanks\/$/);
    await expect(page.getByText('お問い合わせを受け付けました。')).toBeVisible();

    const mails = await waitForMails(request, 1);
    const mail = await getMail(request, mails[0].ID);
    // メールの本文は改行が CRLF になり、行末に空白が付くことがあるため、そろえてから比べる。
    const text = mail.Text.replace(/\r\n/g, '\n').replace(/[ \t]+$/gm, '');
    expect(mail.To.map((to) => to.Address)).toEqual(['admin@example.test']);
    expect(mail.Subject).toBe('【株式会社ひなた】お問い合わせ（山田 太郎 様）');
    expect(mail.ReplyTo.map((to) => to.Address)).toEqual(['yamada@example.test']);
    expect(text).toContain('山田 太郎');
    expect(text).toContain('yamada@example.test');
    expect(text).toContain('Webサイトのリニューアルについて相談したいです。\n予算は相談させてください。');
  });

  const missingRequired = [
    { label: 'お名前', input: { ...validInput, name: '' }, error: 'お名前を入力してください。', field: 'name' },
    { label: 'メールアドレス', input: { ...validInput, email: '' }, error: 'メールアドレスを入力してください。', field: 'email' },
    { label: 'お問い合わせ内容', input: { ...validInput, message: '' }, error: 'お問い合わせ内容を入力してください。', field: 'message' },
  ];
  for (const [index, { label, input, error, field }] of missingRequired.entries()) {
    test(`TC-16-${index + 1} [SPEC-05][SPEC-13] 「${label}」だけが未入力だと送信できない`, async ({ page, request }) => {
      await fillContactForm(page, input);
      await submit(page);

      await expectRejected(page, request, [error]);
      await expect(page.locator(`#error-${field}`)).toHaveText(error);
    });
  }

  test('TC-17 [SPEC-05][SPEC-13] 他の項目を正しく入力しても、同意にチェックがないと送信できない', async ({ page, request }) => {
    await fillContactForm(page, { ...validInput, consent: false });
    await submit(page);

    await expectRejected(page, request, ['プライバシーポリシーへの同意が必要です。']);
    await expect(page.locator('#error-consent')).toHaveText('プライバシーポリシーへの同意が必要です。');
  });

  test('TC-18 [SPEC-05][SPEC-13] すべて未入力で送信すると、4つのエラーメッセージがすべて表示される', async ({ page, request }) => {
    await submit(page);

    await expectRejected(page, request, [
      'お名前を入力してください。',
      'メールアドレスを入力してください。',
      'お問い合わせ内容を入力してください。',
      'プライバシーポリシーへの同意が必要です。',
    ]);
  });

  for (const [index, email] of ['yamada', '@example.test', 'yamada@', 'yamada@example'].entries()) {
    test(`TC-19-${index + 1} [SPEC-12] メールアドレスが「${email}」だと送信できない`, async ({ page, request }) => {
      await fillContactForm(page, { ...validInput, email });
      await submit(page);

      await expectRejected(page, request, ['メールアドレスの形式が正しくありません。']);
    });
  }

  test('TC-20 [SPEC-05] 空白（全角スペース・半角スペース・改行）だけの入力は未入力とみなされる', async ({ page, request }) => {
    await fillContactForm(page, { ...validInput, name: '　 　', message: ' \n　' });
    await submit(page);

    await expectRejected(page, request, ['お名前を入力してください。', 'お問い合わせ内容を入力してください。']);
  });

  test('TC-21 [SPEC-13] 送信できなかった場合、入力済みの内容が残っている', async ({ page, request }) => {
    await fillContactForm(page, { ...validInput, consent: false });
    await submit(page);

    await expectRejected(page, request, ['プライバシーポリシーへの同意が必要です。']);
    await expect(page.getByLabel('お名前')).toHaveValue(validInput.name);
    await expect(page.getByLabel('メールアドレス')).toHaveValue(validInput.email);
    await expect(page.getByLabel('お問い合わせ内容')).toHaveValue(validInput.message);
  });
});
