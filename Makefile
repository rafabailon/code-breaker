.PHONY: install test play docker-install docker-test docker-play docker-clean clean

install:
	composer install

test:
	composer test

play:
	php bin/play_game.php

docker-install:
	docker compose run --rm app composer install

docker-test:
	docker compose run --rm app composer test

docker-play:
	docker compose run --rm app php bin/play_game.php

docker-clean:
	docker compose down --remove-orphans

clean:
	rm -rf .phpunit.cache
