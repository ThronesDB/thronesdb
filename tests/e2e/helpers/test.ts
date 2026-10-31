import { test as base, expect, Browser, BrowserContext, Page } from '@playwright/test';
import { storageFor, Role } from './users';
import { baseURL } from '../playwright.config';

/**
 * The Playwright `test`, plus checks that run around every test:
 * - the cookie-consent banner is pre-dismissed so it never covers the page
 * - an uncaught JavaScript error on the page fails the test
 * - any 5xx response from the site itself fails the test
 * Failures of third-party requests (CDNs, analytics) are ignored here.
 */
async function dismissCookieBanner(context: BrowserContext) {
  const es = new URL(baseURL);
  es.hostname = `es.${es.hostname}`;
  await context.addCookies([
    { name: 'cookieconsent_status', value: 'dismiss', url: baseURL },
    { name: 'cookieconsent_status', value: 'dismiss', url: es.toString() },
  ]);
}

function watchForErrors(page: Page, errors: string[]) {
  const origin = new URL(baseURL).hostname;
  page.on('pageerror', (e) => errors.push(`JS error: ${e.message}`));
  page.on('response', (r) => {
    const host = new URL(r.url()).hostname;
    if (r.status() >= 500 && host.endsWith(origin)) errors.push(`${r.status()} ${r.url()}`);
  });
}

type Fixtures = {
  siteErrors: string[];
  /** Opens a page logged in as another test account (same error checks apply). */
  pageAs: (role: Role) => Promise<Page>;
};

export const test = base.extend<Fixtures>({
  context: async ({ context }, use) => {
    await dismissCookieBanner(context);
    await use(context);
  },
  siteErrors: [
    async ({ page }, use) => {
      const errors: string[] = [];
      watchForErrors(page, errors);
      await use(errors);
      expect(errors, 'JavaScript errors or server errors during the test').toEqual([]);
    },
    { auto: true },
  ],
  pageAs: async ({ browser, siteErrors }, use) => {
    const contexts: BrowserContext[] = [];
    await use(async (role: Role) => {
      const context = await browser.newContext({ storageState: storageFor(role) });
      contexts.push(context);
      await dismissCookieBanner(context);
      const page = await context.newPage();
      watchForErrors(page, siteErrors);
      return page;
    });
    for (const c of contexts) await c.close();
  },
});

export { expect };

/** Unique suffix for names created by this run. */
export const runId = Date.now().toString(36);

/**
 * Waits until the page's card database (ForerunnerDB, filled by app.data.js)
 * has loaded. The deckbuilder and importer use it, and fail if driven earlier.
 */
export async function waitForCardData(page: Page) {
  await page.waitForFunction(() => (window as any).app?.data?.isLoaded === true);
}

/** Logs in through the login form. */
export async function login(page: Page, username: string, password: string) {
  await page.goto('/login');
  await page.fill('#username', username);
  await page.fill('#password', password);
  await Promise.all([page.waitForURL((u) => !u.pathname.startsWith('/login')), page.click('#_submit')]);
}
