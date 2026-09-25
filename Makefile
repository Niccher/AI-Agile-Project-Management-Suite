.PHONY: help up down restart build logs ps bash-web bash-ml migrate seed

help:
	@echo "AI-Agile-Project-Management-Suite Commands:"
	@echo "  make up         Start all services in background"
	@echo "  make down       Stop all services"
	@echo "  make restart    Restart all services"
	@echo "  make build      Rebuild and start all containers"
	@echo "  make logs       Tail logs from all services"
	@echo "  make ps         List status of running containers"
	@echo "  make bash-web   Enter bash shell in the WebApp container"
	@echo "  make bash-ml    Enter bash shell in the ML container"
	@echo "  make migrate    Run CodeIgniter database migrations"
	@echo "  make seed       Run CodeIgniter database seeders"

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

build:
	docker compose up --build -d

logs:
	docker compose logs -f

ps:
	docker compose ps

bash-web:
	docker compose exec chege-jira bash

bash-ml:
	docker compose exec ml-chege-jira bash

migrate:
	docker compose exec chege-jira php spark migrate --all

seed:
	docker compose exec chege-jira php spark db:seed DemoSeeder
