import { execFileSync } from 'child_process';
import { test, expect, runId, snap } from '../helpers/test';
import { loadUsers, storageFor } from '../helpers/users';

// The OAuth2 server other apps use to act on a user's decks: register a
// client, have a user approve it, exchange the code for a token, call the
// protected API with it.
//
// Registering the client needs the console, so this runs `docker exec` on
// the app container (E2E_APP_CONTAINER, default "thronesdb"); it is skipped
// when that container isn't reachable.

test.describe.configure({ mode: 'serial' });
test.use({ storageState: storageFor('author') });

const container = process.env.E2E_APP_CONTAINER ?? 'thronesdb';
const clientId = `e2e${runId}`;
const clientSecret = `e2e-secret-${runId}`;
const redirectUri = 'http://localhost/e2e-oauth-callback';

function console(...args: string[]) {
  return execFileSync('docker', ['exec', '-u', 'www-data', container, 'php', 'bin/console', ...args], { encoding: 'utf8' });
}

test.beforeAll(() => {
  try {
    console('league:oauth2-server:create-client', `E2E client ${runId}`, clientId, clientSecret,
      `--redirect-uri=${redirectUri}`, '--grant-type=authorization_code', '--grant-type=refresh_token',
      '--scope=read_deck', '--scope=write_deck');
  } catch (e) {
    test.skip(true, `could not register an OAuth2 client through docker exec on "${container}"`);
  }
});

test('a user approves a client, which then reads their decks with the token', async ({ page, request }) => {
  const { author } = loadUsers();
  const authorize = `/oauth/v2/authorize?${new URLSearchParams({
    response_type: 'code', client_id: clientId, redirect_uri: redirectUri, scope: 'read_deck write_deck', state: runId,
  })}`;

  await page.goto(authorize);
  await expect(page.locator('body')).toContainText(`E2E client ${runId}`);
  await expect(page.locator('body')).toContainText('Read your decks');
  await snap(page, 'consent page');

  // Approving sends the browser to the client's redirect URI with a code
  const callback = page.waitForRequest((r) => r.url().startsWith(redirectUri));
  await page.getByRole('button', { name: 'Allow' }).click();
  const callbackUrl = new URL((await callback).url());
  expect(callbackUrl.searchParams.get('state')).toBe(runId);
  const code = callbackUrl.searchParams.get('code');
  expect(code).toBeTruthy();

  const tokenRes = await request.post('/oauth/v2/token', {
    form: { grant_type: 'authorization_code', client_id: clientId, client_secret: clientSecret, redirect_uri: redirectUri, code: code! },
  });
  expect(tokenRes.status(), await tokenRes.text()).toBe(200);
  const token = await tokenRes.json();
  expect(token.access_token).toBeTruthy();
  expect(token.refresh_token).toBeTruthy();

  const auth = { Authorization: `Bearer ${token.access_token}` };
  const me = await request.get('/api/oauth2/user', { headers: auth });
  expect(me.status()).toBe(200);
  expect((await me.json()).data[0].username).toBe(author.username);

  const decks = await request.get('/api/oauth2/decks', { headers: auth });
  expect(decks.status()).toBe(200);
  expect(Array.isArray(await decks.json())).toBe(true);

  // The refresh token works too
  const refreshed = await request.post('/oauth/v2/token', {
    form: { grant_type: 'refresh_token', client_id: clientId, client_secret: clientSecret, refresh_token: token.refresh_token },
  });
  expect(refreshed.status()).toBe(200);
});

test('the API refuses requests without a valid token', async ({ request }) => {
  expect((await request.get('/api/oauth2/decks')).status()).toBe(401);
  expect((await request.get('/api/oauth2/decks', { headers: { Authorization: 'Bearer not-a-token' } })).status()).toBe(401);
});

test('denying the request sends the user back with access_denied', async ({ page }) => {
  await page.goto(`/oauth/v2/auth?${new URLSearchParams({
    response_type: 'code', client_id: clientId, redirect_uri: redirectUri, scope: 'read_deck', state: runId,
  })}`);
  const callback = page.waitForRequest((r) => r.url().startsWith(redirectUri));
  await page.getByRole('button', { name: 'Deny' }).click();
  expect(new URL((await callback).url()).searchParams.get('error')).toBe('access_denied');
});
