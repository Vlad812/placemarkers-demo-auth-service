AUTH_SERVICE_DIR := $(patsubst %/,%,$(dir $(abspath $(lastword $(MAKEFILE_LIST)))))
PROJECT_ROOT := $(abspath $(AUTH_SERVICE_DIR)/../..)
include $(PROJECT_ROOT)/config.mk

.PHONY: auth-service-init auth-service-build auth-service-up auth-service-down auth-service-cache-clear auth-service-test-unit

auth-service-init:
	@echo "composer зависимости"
	docker compose -f $(AUTH_SERVICE_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) run --rm auth-service-cli composer install --optimize-autoloader --no-interaction
	@echo 'Обновляю автозагрузчик Composer...';
	docker compose -f $(AUTH_SERVICE_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) run --rm auth-service-cli composer dump-autoload --optimize;
	@echo 'Генерирую JWT ключи...';
	docker compose -f $(AUTH_SERVICE_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) run --rm auth-service-cli php bin/console lexik:jwt:generate-keypair --skip-if-exists

auth-service-build:
	@echo build auth-service
	docker compose -f $(AUTH_SERVICE_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) build

auth-service-up:
	@echo up auth-service
	docker compose -f $(AUTH_SERVICE_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) up -d auth-service-app

auth-service-down:
	@echo down auth-service
	docker compose -f $(AUTH_SERVICE_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) down -v

auth-service-cache-clear:
	@echo clear Symfony cache
	docker compose -f $(AUTH_SERVICE_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) run --rm auth-service-cli sh -c "rm -rf var/cache/* && php bin/console cache:warmup"

auth-service-test-unit:
	docker compose -f $(AUTH_SERVICE_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) run --rm auth-service-cli vendor/bin/phpunit tests/Unit
