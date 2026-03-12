init:
	sudo apt install php-xml
install:
	composer install
test:
	./vendor/bin/phpunit --colors --testdox integration/

test-only:
	./vendor/bin/phpunit  --colors --testdox integration/ --group 'only'