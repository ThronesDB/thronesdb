#!/bin/bash
# Entrypoint for every role the image runs. The command picks the role:
#   apache2-foreground  the web app plus its scheduled jobs (default)
#   init                first-time setup (see app-init)
#   anything else       run as given, e.g. "php bin/console ..."
set -e

cd /var/www/html

console() {
    runuser -u www-data -- php bin/console "$@"
}

# var/ may be a host bind mount (sessions) created as root
mkdir -p var/cache var/log var/sessions
chown -R www-data:www-data var

# OAuth2 signing keys (config/packages/league_oauth2_server.yaml). Created on
# first start; var/oauth is a host bind mount so they survive rebuilds.
if [ ! -f var/oauth/private.key ]; then
    mkdir -p var/oauth
    php -r '$k = openssl_pkey_new(["private_key_bits" => 2048, "private_key_type" => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($k, $pem); file_put_contents("var/oauth/private.key", $pem);
        file_put_contents("var/oauth/public.key", openssl_pkey_get_details($k)["key"]);'
    echo "Created OAuth2 key pair in var/oauth"
fi
chown -R www-data:www-data var/oauth
chmod 600 var/oauth/private.key

case "$1" in
    apache2-foreground)
        php /usr/local/lib/app/dbcheck.php wait
        if [ "${RUN_MIGRATIONS:-0}" = "1" ]; then
            console doctrine:migrations:migrate --no-interaction --allow-no-migration
        fi
        console cache:warmup --no-interaction

        # Two processes side by side: Apache serves the site, supercronic runs
        # the scheduled jobs in /etc/app.crontab as www-data. If either one
        # exits, stop the other and exit too, so Docker restarts the whole
        # container rather than leaving it running half broken.
        docker-php-entrypoint "$@" &
        web=$!
        setpriv --reuid=www-data --regid=www-data --init-groups supercronic /etc/app.crontab &
        jobs=$!

        stop_both() {
            # SIGWINCH is Apache's graceful stop (the image's STOPSIGNAL, so
            # it is also what "docker stop" sends)
            kill -WINCH "$web" 2>/dev/null || true
            kill -TERM "$jobs" 2>/dev/null || true
        }
        stopping=0
        trap 'stopping=1; stop_both' TERM INT WINCH

        set +e
        wait -n "$web" "$jobs"
        status=$?
        if [ "$stopping" = 1 ]; then
            # Asked to stop: report Apache's own exit status, not the signal's
            wait "$web"
            status=$?
        else
            # One of them died on its own: take the other down with it
            stop_both
        fi
        wait
        exit "$status"
        ;;
    init)
        exec app-init
        ;;
    *)
        exec docker-php-entrypoint "$@"
        ;;
esac
