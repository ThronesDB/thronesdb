#!/bin/bash
set -e

echo "Installing PHP dependencies..."
composer install

echo "Installing Node dependencies..."
npm install

echo "Creating database..."
php bin/console doctrine:database:create --if-not-exists

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "Loading fixtures..."
php bin/console doctrine:fixtures:load --env=dev

echo "Cloning json data repository..."
if [ ! -d "/workspace/throneteki-json-data" ]; then
  git clone https://github.com/throneteki-playtesting/throneteki-json-data.git -b master throneteki-json-data
fi

echo "Importing json data..."
php bin/console app:import:std /workspace/throneteki-json-data --no-interaction

echo "Importing restriction lists..."
php bin/console app:restrictions:import /workspace/throneteki-json-data --no-interaction

echo "Activating restriction list..."
php bin/console app:restrictions:activate --no-interaction

echo "Dumping translations..."
php bin/console bazinga:js-translation:dump assets/js

echo "Dumping routes..."
php bin/console fos:js-routing:dump --target=public/js/fos_js_routes.js

echo "Building assets..."
npx gulp

echo "Clearing cache..."
php bin/console cache:clear --env=dev

echo "Setup complete!"
