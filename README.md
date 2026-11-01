ThronesDB
=======

# Running with Docker

The `docker/` folder has an image and compose setup that runs the site, its scheduled jobs and its database, and does the first-time setup below in one command. See [docker/README.md](docker/README.md).

`docker/check.sh` (or `make check`) runs PHPUnit, the lints, PHPStan and PHP_CodeSniffer in Docker. Browser smoke tests (Playwright) that run against the stack are in [tests/e2e](tests/e2e/README.md).

# Very quick guide on how to install a local copy

This guide assumes you know how to use the command-line and that your machine has PHP 8.4, MySQL 8.4 and Node.js 22 installed.

- install [composer](https://getcomposer.org/download/)
- install [npm](https://www.npmjs.com/get-npm) 
- install [gulp](https://gulpjs.com/docs/en/getting-started/quick-start/#install-the-gulp-command-line-utility)
- clone the [data repository](https://github.com/throneteki/throneteki-json-data)
- clone this repo
- `cd` to it
- run `composer install` to install PHP dependencies
- run `npm install` to install JS dependencies
- copy the `.env` file to `.env.local` and modify its configuration settings to your needs 
- create the OAuth2 signing keys: `mkdir -p var/oauth && openssl genrsa -out var/oauth/private.key 2048 && openssl rsa -in var/oauth/private.key -pubout -out var/oauth/public.key`
- run `php bin/console doctrine:database:create` to create the database
- run `php bin/console doctrine:migrations:migrate` to create the database schema
- run `php bin/console doctrine:fixtures:load --env=prod` to load default application data
- run `php bin/console app:import:std ../throneteki-json-data` or whatever the path to the data repository is to load cards and packs data
- run `php bin/console app:restrictions:import ../throneteki-json-data` or whatever the path to the data repository is to load restricted lists
- run `php bin/console app:restrictions:activate` to activate any restricted lists that apply
- run `php bin/console bazinga:js-translation:dump assets/js` to export translation files for the frontend
- run `php bin/console fos:js-routing:dump --target=public/js/fos_js_routes.js` to export routes for the frontend
- run `npx gulp` to build web assets

## Setup an admin account

- register (or run `php bin/console fos:user:create <username>`)
- make sure your account is enabled (or run `php bin/console fos:user:activate <username>`)
- run `php bin/console fos:user:promote --super <username>`
