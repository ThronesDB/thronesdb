# Browser smoke tests

[Playwright](https://playwright.dev) tests that drive the site in a real browser, against the Docker stack in `docker/`. They check that the main flows still work end to end: registration and email confirmation, login, password reset, the deckbuilder, text import, publishing, likes, favourites, comments, search, the public API and the Spanish site.

They are a "did we break it" check, meant to be run before and after any larger change (a dependency upgrade, a refactor).

## Setup (once)

Needs Node 18+ and Docker.

```bash
cd tests/e2e
npm install
npx playwright install chromium
```

The stack needs `docker/.env` and `docker/app.env` (see `docker/README.md`). Then start it with the test overlay, which keeps the production environment but serves the site as `localhost` and catches all mail in Mailpit:

```bash
npm run stack:up      # build and start (docker-compose.yml + docker-compose.e2e.yml)
npm run stack:init    # first time only: schema, default data, cards
```

## Running

```bash
npm test              # the smoke suite, about a minute
npm run report        # open the newest run's HTML report
```

Every run keeps a full record, passing or not, in its own folder under `reports/` (named by local start time):

- named full-page screenshots at the key steps of each test ("01 decklist published", "02 after reload: counts and comment kept"), plus one of the final state
- a trace of each test: open it from the report to replay every step with before/after snapshots of the page, the network requests and the console

`npm run report -- <folder>` opens an older run. A run is about 35 MB, mostly traces; `npm run reports:clean` keeps the newest 10.

Each run registers two fresh accounts (`e2e_author_<id>`, `e2e_voter_<id>`) through the real registration form and confirmation email, then uses them for everything else, so runs don't depend on each other or on existing data. They do leave their decks, decklists and comments behind: run against a disposable database, not production.

Point the tests at a different stack with `E2E_BASE_URL` (default `http://localhost:8000`) and `E2E_MAILPIT_URL` (default `http://localhost:8025`). The Spanish test needs a hostname, not an IP address, since Spanish is served from `es.<hostname>`.

## Screenshot comparisons

A separate project compares screenshots of a few key pages (login, card page, search results, new deck page). Baselines aren't committed, because fonts, OS and CDN availability all change the pixels. Use it to compare two versions on the same machine:

```bash
npm run test:visual:baseline   # on the known-good version: record
# ...switch versions, rebuild the stack...
npm run test:visual            # compare; differences are in the HTML report
```

## Layout

| Path | What |
|---|---|
| `tests/auth.setup.ts` | registers and confirms the two test accounts, saves their logins |
| `tests/public.spec.ts` | pages and API anyone can see |
| `tests/account.spec.ts` | login, logout, wrong password, password reset email |
| `tests/decks.spec.ts` | building a deck, importing one from text, deleting |
| `tests/decklists.spec.ts` | publishing, likes, favourites, comments, search, the decklist API |
| `tests/visual.spec.ts` | screenshot comparisons (`npm run test:visual`) |
| `helpers/test.ts` | the `test` used by every spec: fails on any JavaScript error or server error, hides the cookie banner; `snap()` for step screenshots |
| `open-report.js` | `npm run report` / `reports:clean` |
| `helpers/mailpit.ts` | reads mail from Mailpit's API |
| `fixtures/stark-core.txt` | a legal Core Set deck in the text import format |

## Notes for writing tests

- Import `test` and `expect` from `helpers/test.ts`, not from `@playwright/test`, so the error checks apply.
- Call `snap(page, 'what this shows')` after each step worth looking at later; it lands in the report under that name.
- The deckbuilder and the importer load the card pool into the browser first; call `waitForCardData(page)` before using them.
- The site loads jQuery, Bootstrap's JS and other libraries from public CDNs. If the browser can't reach them, every page fails with JavaScript errors. Behind an HTTP proxy, set `E2E_BROWSER_PROXY`.
