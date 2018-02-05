install:
	composer install
	php bin/console bazinga:js-translation:dump
	php bin/console assets:install --symlink web
	bin/console assetic:dump

test:
	vendor/bin/phpunit