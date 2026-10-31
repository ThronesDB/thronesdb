import { test, expect, login, snap } from '../helpers/test';
import { waitForMail, findLink } from '../helpers/mailpit';
import { loadUsers } from '../helpers/users';

// Logging in and out, and the password reset email. Uses the author account
// registered by auth.setup.ts, from a fresh (logged-out) browser.

test('log in and log out', async ({ page }) => {
  const { author } = loadUsers();
  await login(page, author.username, author.password);
  // Pages that need a login now open
  const res = await page.goto('/decks');
  expect(res?.status()).toBe(200);
  await expect(page).toHaveURL(/\/decks/);
  await snap(page, 'logged in: my decks');

  await page.goto('/logout');
  await page.goto('/decks');
  await expect(page).toHaveURL(/\/login/);
});

test('a wrong password is refused', async ({ page }) => {
  const { author } = loadUsers();
  await page.goto('/login');
  await page.fill('#username', author.username);
  await page.fill('#password', 'not-the-password');
  await page.click('#_submit');
  await expect(page).toHaveURL(/\/login/);
  await expect(page.locator('.alert-danger, .alert-error')).toBeVisible();
  await snap(page, 'wrong password refused');
});

test('password reset sends an email with a working link', async ({ page }) => {
  const { voter } = loadUsers();
  await page.goto('/resetting/request');
  await page.fill('#username', voter.username);
  await page.click('form[action*="resetting/send-email"] [type=submit]');
  await expect(page).toHaveURL(/\/resetting\/check-email/);

  const mail = await waitForMail(voter.email, /reset/i);
  const resetUrl = findLink(mail.text, '/resetting/reset/');
  await page.goto(resetUrl);
  await expect(page.locator('#fos_user_resetting_form_plainPassword_first')).toBeVisible();
  await snap(page, 'reset link opens the new-password form');
});
