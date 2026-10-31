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

# Outgoing mail: SwiftMailer hands messages to msmtp (config/packages/swiftmailer.yaml).
# Write its config from SMTP_* variables; without SMTP_HOST mail is not sent.
if [ -n "${SMTP_HOST:-}" ]; then
    {
        echo "defaults"
        echo "logfile -"
        echo "account default"
        echo "host ${SMTP_HOST}"
        echo "port ${SMTP_PORT:-587}"
        echo "from ${SMTP_FROM:-${APP_EMAIL_SENDER_ADDRESS:-no-reply@localhost}}"
        case "${SMTP_TLS:-starttls}" in
            off)      echo "tls off" ;;
            tls)      echo "tls on"; echo "tls_starttls off"; echo "tls_trust_file /etc/ssl/certs/ca-certificates.crt" ;;
            *)        echo "tls on"; echo "tls_starttls on"; echo "tls_trust_file /etc/ssl/certs/ca-certificates.crt" ;;
        esac
        if [ -n "${SMTP_USER:-}" ]; then
            echo "auth on"
            echo "user ${SMTP_USER}"
            echo "password ${SMTP_PASSWORD:-}"
        else
            echo "auth off"
        fi
    } > /etc/msmtprc
    chown root:www-data /etc/msmtprc
    chmod 640 /etc/msmtprc
fi

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
