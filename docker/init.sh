#!/bin/sh
# First-time setup, run once against an empty database:
#   docker compose --profile init run --rm init
#
# Creates the schema, loads the default data (factions, types, rarities,
# tournaments), imports cards, packs and restricted lists from the card data
# repository, and optionally creates an admin account.
#
# If cards are already present it stops before loading anything, because
# loading the default data empties the database. Set INIT_FORCE=1 to wipe
# and reload anyway.
set -e

cd /var/www/html

console() {
    runuser -u www-data -- php bin/console "$@"
}

php /usr/local/lib/app/dbcheck.php wait

echo "== Running migrations"
console doctrine:migrations:migrate --no-interaction --allow-no-migration

if php /usr/local/lib/app/dbcheck.php has-cards && [ "${INIT_FORCE:-0}" != "1" ]; then
    echo "== Cards already present; skipping default data and card import (INIT_FORCE=1 to reload)"
else
    echo "== Loading default data"
    console doctrine:fixtures:load --no-interaction

    data_dir="${CARD_DATA_DIR:-/srv/carddata}"
    git config --global --add safe.directory "$data_dir"
    if [ ! -d "$data_dir/.git" ] && [ -z "$(ls -A "$data_dir" 2>/dev/null)" ]; then
        echo "== Cloning card data from ${CARD_DATA_REPO}"
        git clone --depth 1 "${CARD_DATA_REPO}" "$data_dir"
    elif [ -d "$data_dir/.git" ]; then
        echo "== Updating card data"
        git -C "$data_dir" pull --ff-only || echo "(pull failed; using the copy on disk)"
    fi

    echo "== Importing cards and packs"
    console app:import:std "$data_dir" --no-interaction
    echo "== Importing restricted lists"
    console app:restrictions:import "$data_dir" --no-interaction
    console app:restrictions:activate --no-interaction
fi

if [ -n "${ADMIN_USERNAME:-}" ]; then
    echo "== Admin account ${ADMIN_USERNAME}"
    if php /usr/local/lib/app/dbcheck.php has-user "${ADMIN_USERNAME}"; then
        echo "(already exists; left unchanged)"
    else
        console fos:user:create "${ADMIN_USERNAME}" "${ADMIN_EMAIL}" "${ADMIN_PASSWORD}" --no-interaction
        console fos:user:activate "${ADMIN_USERNAME}"
        console fos:user:promote --super "${ADMIN_USERNAME}"
    fi
fi

echo "== Done"
