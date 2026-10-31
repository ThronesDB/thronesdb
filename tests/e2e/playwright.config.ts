import { defineConfig, devices } from '@playwright/test';

// The stack must already be running (see README.md). These are the URLs the
// tests use; override them to point at another stack.
export const baseURL = process.env.E2E_BASE_URL ?? 'http://localhost:8000';
export const mailpitURL = process.env.E2E_MAILPIT_URL ?? 'http://localhost:8025';

// Only for environments where the browser must reach the public CDNs the site
// loads (jQuery, Bootstrap, Font Awesome...) through an HTTP proxy.
const proxy = process.env.E2E_BROWSER_PROXY
  ? { server: process.env.E2E_BROWSER_PROXY, bypass: 'localhost,*.localhost,127.0.0.1' }
  : undefined;

// Each run's HTML report goes in its own folder under reports/, named by
// start time, so older runs stay around to look back at. Set once and
// inherited by the worker processes.
const now = new Date();
const pad = (n: number) => String(n).padStart(2, '0');
process.env.E2E_RUN_STAMP ??=
  `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}_` +
  `${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;
const reportDir = `reports/${process.env.E2E_RUN_STAMP}`;

export default defineConfig({
  testDir: './tests',
  // The tests share one database and build on each other's data within a
  // file, so run them one at a time.
  fullyParallel: false,
  workers: 1,
  retries: 0,
  timeout: 60_000,
  expect: { timeout: 10_000 },
  reporter: [['list'], ['html', { open: 'never', outputFolder: reportDir }]],
  use: {
    baseURL,
    proxy,
    viewport: { width: 1280, height: 900 },
    // Recorded for every test, passing or not, and kept in the HTML report
    // (npm run report): a screenshot of the final state, plus a trace that
    // replays each step with before/after snapshots of the page. Tests also
    // attach named screenshots at key steps (snap() in helpers/test.ts).
    trace: 'on',
    screenshot: { mode: 'on', fullPage: true },
  },
  projects: [
    // Registers the test accounts (through the real registration + email
    // confirmation flow) that the other projects log in as.
    { name: 'setup', testMatch: /.*\.setup\.ts/, use: { ...devices['Desktop Chrome'] } },
    {
      name: 'smoke',
      testIgnore: /(\.setup|visual\.spec)\.ts/,
      dependencies: ['setup'],
      use: { ...devices['Desktop Chrome'], viewport: { width: 1280, height: 900 } },
    },
    {
      // Screenshot comparisons; see tests/visual.spec.ts. Not part of the
      // default run (npm test). Record baselines with
      // `npm run test:visual:baseline`, then compare with `npm run test:visual`.
      name: 'visual',
      testMatch: /visual\.spec\.ts/,
      dependencies: ['setup'],
      use: { ...devices['Desktop Chrome'], viewport: { width: 1280, height: 900 } },
    },
  ],
});
