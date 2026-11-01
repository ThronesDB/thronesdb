import fs from 'fs';
import path from 'path';
import { test, expect, runId, waitForCardData, snap } from '../helpers/test';
import { storageFor } from '../helpers/users';

// Private decks: build one in the deckbuilder, import one from text, delete.

test.use({ storageState: storageFor('author') });

const fixture = (name: string) => fs.readFileSync(path.join(__dirname, '..', 'fixtures', name), 'utf8');

test('build a deck in the deckbuilder, save it, then delete it', async ({ page }) => {
  const deckName = `E2E built ${runId}`;

  await page.goto('/deck/new');
  await page.check('input[name=faction][value=stark]');
  await snap(page, 'new deck: Stark chosen');
  await page.getByRole('button', { name: /Create the deck/ }).click();
  await expect(page).toHaveURL(/\/deck\/edit\//);
  await waitForCardData(page);

  // Add one copy each of the first three cards in the card table
  const ones = page.locator('#collection-table input[type=radio][value="1"]');
  await expect(ones.first()).toBeAttached();
  for (let i = 0; i < 3; i++) {
    await ones.nth(i).evaluate((el: HTMLInputElement) => (el.closest('label') ?? el).click());
  }

  await snap(page, 'deckbuilder with three cards added');
  const form = page.locator('form:has(input.decklist-name)');
  await form.locator('input[name=name]').fill(deckName);
  await form.locator('button[type=submit].btn-success').click();
  await expect(page).toHaveURL(/\/decks/);

  await page.getByRole('link', { name: deckName }).first().click();
  await expect(page).toHaveURL(/\/deck\/view\//);
  await expect(page.locator('body')).toContainText(deckName);
  await expect(page.locator('body')).toContainText(/(Draw|Plot) deck: [1-9]/);
  await snap(page, 'saved deck');

  await page.click('#btn-delete');
  await page.locator('#delete-deck-form [type=submit]').click();
  await expect(page).toHaveURL(/\/decks/);
  await expect(page.getByRole('link', { name: deckName })).toHaveCount(0);
  await snap(page, 'my decks after deleting it');
});

test('import a deck from text', async ({ page }) => {
  const deckName = `E2E imported ${runId}`;
  const text = fixture('stark-core.txt').replace(/^.*$/m, deckName);

  await page.goto('/deck/import');
  await waitForCardData(page);
  await page.fill('#content', text);
  await page.click('#btn-import');
  await expect(page.locator('body')).toContainText('Draw deck: 60 cards');
  await expect(page.locator('body')).toContainText('Plot deck: 7 cards');
  await snap(page, 'import parsed');

  await page.click('#btn-save');
  await expect(page).toHaveURL(/\/decks/);
  await page.getByRole('link', { name: deckName }).first().click();
  await expect(page).toHaveURL(/\/deck\/view\//);
  // A legal deck can be published
  await expect(page.locator('#btn-publish')).toBeVisible();
  await snap(page, 'imported deck');
});
