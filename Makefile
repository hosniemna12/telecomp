# Laravel Docker Commands

.PHONY: help build up down logs migrate seed fresh shell artisan

help:
	@echo "Available commands:"
	@echo "  make build       - Build Docker images"
	@echo "  make up          - Start all containers"
	@echo "  make down        - Stop all containers"
	@echo "  make restart     - Restart all containers"
	@echo "  make logs        - View application logs"
	@echo "  make migrate     - Run database migrations"
	@echo "  make seed        - Run database seeders"
	@echo "  make fresh       - Refresh database"
	@echo "  make shell       - Open Laravel shell"
	@echo "  make artisan     - Run artisan command (use CMD=\"make artisan -- your:command\")"
	@echo "  make tinker      - Open Laravel Tinker"
	@echo "  make test        - Run PHP tests"
	@echo "  make format      - Format code with Pint"
	@echo "  make lint        - Lint code"

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

restart:
	docker-compose restart

logs:
	docker-compose logs -f app

migrate:
	docker-compose exec app php artisan migrate

seed:
	docker-compose exec app php artisan db:seed

fresh:
	docker-compose exec app php artisan migrate:fresh --seed

shell:
	docker-compose exec app bash

artisan:
	docker-compose exec app php artisan $(CMD)

tinker:
	docker-compose exec app php artisan tinker

test:
	docker-compose exec app php artisan test

format:
	docker-compose exec app ./vendor/bin/pint

lint:
	docker-compose exec app ./vendor/bin/phpstan analyse

npm:
	docker-compose exec -T node npm $(CMD)

python:
	docker-compose exec python python $(CMD)

clean:
	docker-compose down -v

ps:
	docker-compose ps
