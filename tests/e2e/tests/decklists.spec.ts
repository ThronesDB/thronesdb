import fs from 'fs';
import path from 'path';
import { test, expect, runId, waitForCardData } from '../helpers/test';
import { storageFor } from '../helpers/users';

// Publishing a deck as a decklist, and what other users can do with it:
// like, favourite, comment, find it in search; plus its public API entry.
// The tests build on each other, so they run in order.

test.describe.configure({ mode: 'serial' });
test.use({ storageState: storageFor('author') });

const decklistName = `E2E decklist ${runId}`;
let decklistUrl = '';
let decklistId = '';

test('publish a deck as a decklist', async ({ page }) => {
  const deckName = `E2E to publish ${runId}`;
  const text = fs
    .readFileSync(path.join(__dirname, '..', 'fixtures', 'stark-core.txt'), 'utf8')
    .replace(/^.*$/m, deckName);

  await page.goto('/deck/import');
  await waitForCardData(page);
  await page.fill('#content', text);
  await page.click('#btn-import');
  await expect(page.locator('body')).toContainText('Draw deck: 60 cards');
  await page.click('#btn-save');
  await expect(page).toHaveURL(/\/decks/);
  await page.getByRole('link', { name: deckName }).first().click();

  await page.click('#btn-publish');
  await expect(page).toHaveURL(/\/deck\/publish\//);
  await page.fill('#name', decklistName);
  await page.fill('#descriptionMd', 'Published by the **smoke test**.');
  await page.click('form[action="/decklist/create"] button[type=submit]');

  await expect(page).toHaveURL(/\/decklist\/view\/(\d+)\//);
  await expect(page.locator('h1')).toContainText(decklistName);
  await expect(page.locator('body')).toContainText('Published by the smoke test.');
  decklistUrl = page.url();
  decklistId = decklistUrl.match(/\/decklist\/view\/(\d+)\//)![1];

  // Authors can't like their own decklist
  await expect(page.locator('#social-icon-like')).toHaveCount(0);
});

test('another user likes, favourites and comments on it', async ({ pageAs }) => {
  const page = await pageAs('voter');
  await page.goto(decklistUrl);

  const like = page.locator('#social-icon-like');
  await expect(like.locator('.num')).toHaveText('0');
  await like.click();
  await expect(like.locator('.num')).toHaveText('1');

  const favorite = page.locator('#social-icon-favorite');
  await expect(favorite.locator('.num')).toHaveText('0');
  await favorite.click();
  await expect(favorite.locator('.num')).toHaveText('1');

  const comment = `Nice list from run ${runId}`;
  // Type it (not fill) so the Markdown preview renders as it would for a
  // person; the preview re-renders and moves the button, so let it settle.
  await page.locator('#comment-form-text').pressSequentially(comment);
  await expect(page.locator('#comment-form-preview')).toContainText(comment);
  await Promise.all([
    page.waitForResponse((r) => r.url().includes('/user/comment') && r.request().method() === 'POST'),
    page.locator('form:has(#comment-form-text) button[type=submit]').click(),
  ]);
  await expect(page.locator('.alert-success')).toContainText('comment has been posted');

  // The counts are stored, not just updated in the page
  await page.reload();
  await expect(page.locator('#social-icon-like .num')).toHaveText('1');
  await expect(page.locator('#social-icon-favorite .num')).toHaveText('1');
  await expect(page.locator('#social-icon-comment .num')).toHaveText('1');
  await expect(page.locator('.comment-text').filter({ hasText: comment })).toBeVisible();
});

test('the decklist shows up in search and in the favourites list', async ({ page, pageAs }) => {
  await page.goto(`/decklists/find?name=${encodeURIComponent(decklistName)}&sort=likes`);
  await expect(page.getByRole('link', { name: decklistName })).toBeVisible();

  const voter = await pageAs('voter');
  await voter.goto('/decklists/favorites');
  await expect(voter.getByRole('link', { name: decklistName })).toBeVisible();
});

test('the public API serves the decklist', async ({ request }) => {
  const res = await request.get(`/api/public/decklist/${decklistId}`);
  expect(res.status()).toBe(200);
  const decklist = await res.json();
  expect(decklist.name).toBe(decklistName);
  const total = Object.values(decklist.slots as Record<string, number>).reduce((a, b) => a + b, 0);
  expect(total).toBe(67); // 60 draw + 7 plots
});
