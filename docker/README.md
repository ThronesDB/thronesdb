# Running ThronesDB with Docker

One image runs everything. `docker-compose.yml` starts the app and its database:

| Service | Container | What it does |
|---|---|---|
| `app` | `<project>` | Apache + PHP serving the site on `127.0.0.1:${APP_PORT}`, plus the scheduled jobs from `crontab` (run by supercronic alongside Apache; if either stops, the container restarts) |
| `db` | `<project>_db` | MySQL 8.4, data in `data/mysql/` |
| `init` | (one-off, same image) | First-time setup: schema, default data, cards, restricted lists, admin account |

`<project>` is `COMPOSE_PROJECT_NAME` from `.env` (default `thronesdb`). All commands below run from this `docker/` folder.

## First run

```bash
cp .env.example .env            # compose and database settings
cp app.env.example app.env      # the app's settings: APP_SECRET, mail, branding
chmod 600 .env app.env
# fill in both files (passwords, APP_SECRET, SMTP_*, optional ADMIN_*)

docker compose up -d --build
docker compose --profile init run --rm init
```

`init` clones the card data repository (`CARD_DATA_REPO`) into `data/carddata/` and imports it. It refuses to run over a database that already has cards, because loading the default data empties the database; set `INIT_FORCE=1` to wipe and reload on purpose.

The site is then at `http://localhost:${APP_PORT}` if `APP_WEBSITE_URL` is `localhost`. In production the app redirects every other hostname to `APP_WEBSITE_URL`, so set it to the public hostname (and note that Spanish is served from `es.<hostname>`). It is only bound to localhost: put a reverse proxy in front for the public hostname (below).

## After a code change

The code is baked into the image, so a change means a rebuild:

```bash
git pull
docker compose up -d --build
docker exec -u www-data <project> php bin/console doctrine:migrations:migrate --no-interaction
```

Migrations do not run automatically in production; run them after any update that adds one. Logins survive a rebuild (sessions are kept in `data/sessions/`).

## Running console commands

From the host, as the web server user (so cache files keep the right owner):

```bash
docker exec -u www-data <project> php bin/console <command>
```

For example, `fos:user:promote <username> ROLE_ADMIN`, or `app:restrictions:import /path` after copying data in.

## Mail

SwiftMailer sends through msmtp. The entrypoint writes msmtp's config from the `SMTP_*` settings in `app.env`. With `SMTP_HOST` empty nothing is sent, which also means new accounts can't confirm their email.

## Reverse proxy

The app trusts `X-Forwarded-For` and `X-Forwarded-Proto` from private addresses (`TRUSTED_PROXIES` in `docker-compose.yml`), so the proxy must send them. A minimal nginx site:

```nginx
server {
    server_name decks.example.com;
    # listen 443 ssl; ssl_certificate ...;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host              $host;
        proxy_set_header X-Real-IP         $remote_addr;
        proxy_set_header X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## Development

```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
```

(or uncomment `COMPOSE_FILE` in `.env`). This runs Symfony's dev environment with debug on, mounts `src/`, `templates/`, `translations/` and `config/` from the checkout so PHP and Twig edits show up without a rebuild, runs migrations on start, and sends all mail to Mailpit at `http://127.0.0.1:8025`. JavaScript and CSS changes still need `--build`.

## What lives on the host

| Path | Contents |
|---|---|
| `.env`, `app.env` | settings and secrets (never commit) |
| `data/mysql/` | the database's data directory |
| `data/sessions/` | PHP sessions |
| `data/carddata/` | the card data repository used by `init` |

Back up `data/mysql/` (or a `mysqldump` from the `db` container) and the two settings files.

## Tuning

| Setting | Where | Default |
|---|---|---|
| `APACHE_MAX_WORKERS` | `app.env` | 10 Apache workers (each holds a PHP interpreter) |
| `APP_MEM_LIMIT`, `DB_MEM_LIMIT` | `.env` | 1g each |
| PHP settings | `php/app.ini` | memory_limit 256M, opcache on |
