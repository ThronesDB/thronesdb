#!/bin/bash
set -e

echo "Creating user..."
if php bin/console fos:user:create dev dev@thronesdb.com password123 --no-interaction 2>/dev/null; then
    echo "Activating user..."
    php bin/console fos:user:activate dev

    echo "Promoting user..."
    php bin/console fos:user:promote --super dev
else
    echo "User dev already exists, skipping setup"
fi

echo "Setup complete!"