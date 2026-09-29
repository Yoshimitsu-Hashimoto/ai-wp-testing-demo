import { expect, type APIRequestContext } from '@playwright/test';

// 確認用メールボックス（Mailpit）のAPI。ポートは .env の MAILPIT_PORT に合わせる。
const mailpitUrl = process.env.MAILPIT_URL ?? `http://localhost:${process.env.MAILPIT_PORT ?? '8091'}`;

export type MailSummary = {
  ID: string;
  Subject: string;
  To: { Address: string }[];
  ReplyTo: { Address: string }[];
};

export type MailDetail = MailSummary & { Text: string };

/** 確認用メールボックスを空にする。 */
export async function clearMailbox(request: APIRequestContext): Promise<void> {
  const response = await request.delete(`${mailpitUrl}/api/v1/messages`);
  expect(response.ok()).toBeTruthy();
}

/** 届いているメールの一覧（新しい順）。 */
export async function listMails(request: APIRequestContext): Promise<MailSummary[]> {
  const response = await request.get(`${mailpitUrl}/api/v1/messages`);
  expect(response.ok()).toBeTruthy();
  const body = await response.json();
  return body.messages as MailSummary[];
}

/** メールの本文を含む詳細。 */
export async function getMail(request: APIRequestContext, id: string): Promise<MailDetail> {
  const response = await request.get(`${mailpitUrl}/api/v1/message/${id}`);
  expect(response.ok()).toBeTruthy();
  return (await response.json()) as MailDetail;
}

/**
 * メールが届くのを待ってから一覧を返す。送信が遅れる場合に備えて、数秒間くり返し確認する。
 * 届かないことを確かめたい場合は listMails を使う。
 */
export async function waitForMails(request: APIRequestContext, count: number): Promise<MailSummary[]> {
  await expect.poll(async () => (await listMails(request)).length, { timeout: 5_000 }).toBe(count);
  return listMails(request);
}
