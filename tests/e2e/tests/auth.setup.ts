import fs from 'fs';
import { test as setup, expect } from '@playwright/test';
import { waitForMail, findLink } from '../helpers/mailpit';
import { authDir, usersFile, storageFor, Role, User } from '../helpers/users';

// Registers a fresh author and voter for this run through the real
// registration form and confirmation email, and saves their logged-in
// browser state for the other tests.

const runId = Date.now().toString(36);
const users: Record<Role, User> = {
  author: { username: `e2e_author_${runId}`, email: `e2e_author_${runId}@example.test`, password: 'e2e-Password-1' },
  voter: { username: `e2e_voter_${runId}`, email: `e2e_voter_${runId}@example.test`, password: 'e2e-Password-1' },
};

for (const role of ['author', 'voter'] as Role[]) {
  setup(`register and confirm the ${role} account`, async ({ page }) => {
    const user = users[role];

    await page.goto('/register/');
    await page.fill('#fos_user_registration_form_email', user.email);
    await page.fill('#fos_user_registration_form_username', user.username);
    await page.fill('#fos_user_registration_form_plainPassword_first', user.password);
    await page.fill('#fos_user_registration_form_plainPassword_second', user.password);
    await page.click('form[name=fos_user_registration_form] [type=submit]');
    await expect(page).toHaveURL(/\/register\/check-email/);

    const mail = await waitForMail(user.email);
    const confirmUrl = findLink(mail.text, '/register/confirm/');
    await page.goto(confirmUrl);
    await expect(page).toHaveURL(/\/register\/confirmed/);

    fs.mkdirSync(authDir, { recursive: true });
    await page.context().storageState({ path: storageFor(role) });
  });
}

setup.afterAll(() => {
  fs.mkdirSync(authDir, { recursive: true });
  fs.writeFileSync(usersFile, JSON.stringify(users, null, 2));
});
