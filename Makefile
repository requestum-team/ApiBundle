start:
	docker-compose up -d

stop:
	docker-compose down

restart:
	make stop
	make start

install:
	make start
	docker-compose exec -T php sh -c "composer install"

test-unit:
	make start
	docker-compose exec -T php sh -c "/var/www/vendor/bin/phpunit"
