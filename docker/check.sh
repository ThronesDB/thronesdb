#!/usr/bin/env bash
#
# Runs the project's checks in Docker, the same way on any machine with Docker
# (Linux, macOS, or Git Bash on Windows): PHPUnit, the Symfony lints, PHPStan,
# PHP_CodeSniffer, and a build of the production image. Stops at the first
# failure.
#
#   docker/check.sh             build the images, then run everything
#   docker/check.sh --no-build  reuse the images from the last run
#
# Images are tagged thronesdb_check:test and thronesdb_check:app, so repeated
# runs replace them rather than piling up.
set -euo pipefail
cd "$(dirname "$0")/.."

build=1
if [ "${1:-}" = "--no-build" ]; then
    build=0
fi

test_image=thronesdb_check:test
app_image=thronesdb_check:app

step() { printf '\n== %s\n' "$*"; }

if [ "$build" = 1 ]; then
    step "Building the test image (the app with its dev dependencies)"
    docker build -f docker/Dockerfile --target test -t "$test_image" .
fi

step "PHPUnit"
docker run --rm "$test_image"

step "Lint config, translations, templates and container; validate Doctrine mapping"
docker run --rm -e APP_ENV=dev "$test_image" sh -c "
    php bin/console lint:yaml config translations &&
    php bin/console lint:twig templates &&
    php bin/console lint:container &&
    php bin/console doctrine:schema:validate --skip-sync"

step "PHPStan"
docker run --rm -e APP_ENV=dev "$test_image" sh -c "
    php bin/console cache:warmup &&
    vendor/bin/phpstan analyse --no-progress --memory-limit=1G"

step "PHP_CodeSniffer"
docker run --rm "$test_image" vendor/bin/phpcs -n

if [ "$build" = 1 ]; then
    step "Building the production image"
    docker build -f docker/Dockerfile -t "$app_image" .
fi

step "All checks passed"
