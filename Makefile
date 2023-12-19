build: ## build application images
	docker-compose build
up:
	docker-compose up -d
down:
	docker-compose down
sh:
	docker exec -it php8-oro /bin/bash
