import { test, expect, snap } from '../helpers/test';
import { baseURL } from '../playwright.config';

// Pages and API endpoints anyone can see without logging in.

test('home page renders with navigation', async ({ page }) => {
  await page.goto('/');
  await expect(page.locator('nav.navbar')).toBeVisible();
  await expect(page.getByRole('link', { name: 'Decklists' }).first()).toBeVisible();
  await snap(page, 'home page');
  // The built CSS and JS bundles (gulp output) are referenced and load
  const css = await page.locator('link[rel=stylesheet][href*="/css/"]').count();
  const js = await page.locator('script[src*="/js/app"]').count();
  expect(css).toBeGreaterThan(0);
  expect(js).toBeGreaterThan(0);
});

test('card search finds cards', async ({ page }) => {
  await page.goto('/find?q=Eddard');
  await expect(page.locator('body')).toContainText('Eddard Stark');
  await snap(page, 'search results for Eddard');
});

test('a card page shows the card', async ({ page }) => {
  await page.goto('/card/01001');
  await expect(page.locator('body')).toContainText('A Clash of Kings');
  await snap(page, 'card 01001');
});

test('decklist listings load', async ({ page }) => {
  for (const type of ['popular', 'recent', 'halloffame']) {
    const res = await page.goto(`/decklists/${type}`);
    expect(res?.status(), type).toBe(200);
    await snap(page, `decklists: ${type}`);
  }
});

test('anonymous users are sent to the login page for their decks', async ({ page }) => {
  await page.goto('/decks');
  await expect(page).toHaveURL(/\/login/);
});

test('public card API returns the card pool', async ({ request }) => {
  const cards = await (await request.get('/api/public/cards/')).json();
  expect(cards.length).toBeGreaterThan(1000);
  expect(cards[0]).toHaveProperty('code');
  expect(cards[0]).toHaveProperty('name');

  const card = await (await request.get('/api/public/card/01001')).json();
  expect(card.name).toBe('A Clash of Kings');

  const packs = await (await request.get('/api/public/packs/')).json();
  expect(packs.length).toBeGreaterThan(10);
});

test('the Spanish site is served from the es. subdomain', async ({ page }) => {
  const url = new URL(baseURL);
  test.skip(!/[a-z]/i.test(url.hostname), 'needs a hostname, not an IP address');
  url.hostname = `es.${url.hostname}`;
  await page.goto(url.toString());
  await expect(page.locator('html')).toHaveAttribute('lang', 'es');
  await snap(page, 'Spanish home page');
});
