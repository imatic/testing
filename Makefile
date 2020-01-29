.PHONY: test
test: phpunit phpmd phpcs

.PHONY: phpcs
phpcs:
	./vendor/bin/php-cs-fixer fix --dry-run

.PHONY: phpmd
phpmd:
	./vendor/bin/phpmd bin/,src/,tests/ text phpmd.xml

.PHONY: phpunit
phpunit:
	./vendor/bin/phpunit

.PHONY: update-test
update-test:
	composer install

/usr/local/bin/composer:
	curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
