#!/bin/sh
# Entrypoint for every role the image runs. The command picks the role:
#   apache2-foreground  the web app (default)
#   cron                the scheduled jobs in /etc/app.crontab
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
        exec docker-php-entrypoint "$@"
        ;;
    cron)
        php /usr/local/lib/app/dbcheck.php wait
        console cache:warmup --no-interaction
        # setpriv execs in place, so supercronic is PID 1 and reaps its children
        exec setpriv --reuid=www-data --regid=www-data --init-groups supercronic /etc/app.crontab
        ;;
    init)
        exec app-init
        ;;
    *)
        exec docker-php-entrypoint "$@"
        ;;
esac
