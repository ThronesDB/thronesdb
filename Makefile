install:
	composer install
	php bin/console bazinga:js-translation:dump assets/js
	npm ci
	npx gulp

serve:
	php -S 127.0.0.1:8000 -t public

test:
	vendor/bin/phpunit

# All checks (PHPUnit, lints, PHPStan, PHP_CodeSniffer, image build) in Docker
check:
	bash docker/check.sh
