#!/bin/bash
set -e

echo "Creating user..."
php bin/console fos:user:create dev dev@thronesdb.com password123 --no-interaction

echo "Activating user..."
php bin/console fos:user:activate dev

echo "Promoting user..."
php bin/console fos:user:promote --super dev

echo "Setup complete!"