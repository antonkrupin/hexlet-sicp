test:
	php artisan test

test-coverage:
	php artisan test --coverage-clover build/logs/clover.xml

setup: env-prepare sqlite-prepare install key db-prepare

install:
	composer install
	npm install

start:
	heroku local -f Procfile.dev

db-prepare:
	php artisan migrate --seed

lint:
	composer phpcs

lint-fix:
	composer phpcbf

deploy:
	git push heroku master

env-prepare:
	cp -n .env.example .env || true

sqlite-prepare:
	touch database/database.sqlite

key:
	php artisan key:generate

ide-helper:
	php artisan ide-helper:eloquent
	php artisan ide-helper:gen
	php artisan ide-helper:meta
	php artisan ide-helper:mod -n

CONTAINER_NAME ?= php

# Launch app through docker
docker-build:
	docker-compose up -d --build
	docker-compose exec $(CONTAINER_NAME) cp -n .env.docker.example .env || true
	docker-compose exec $(CONTAINER_NAME) composer install && npm install
	docker-compose exec $(CONTAINER_NAME) php artisan key:generate
	docker-compose exec $(CONTAINER_NAME) php artisan migrate --seed

.PHONY: test
