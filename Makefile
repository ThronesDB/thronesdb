install:
	composer install
	php bin/console bazinga:js-translation:dump
	php bin/console assets:install --symlink web
	bin/console assetic:dump

serve:
	php -S 127.0.0.1:8000 -t public public/router.php

test:
	vendor/bin/phpunit