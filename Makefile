isDocker := $(shell docker info > /dev/null 2>&1 && echo 1)
domain := 'writecode'
server := '141.94.22.174'
login := 'debian'
user := $(shell id -u)
group := $(shell id -g)

ifeq ($(isDocker), 1)
	dc := USER_ID=$(user) GROUP_ID=$(group) docker-compose
	dcimport := USER_ID=$(user) GROUP_ID=$(group) docker-compose -f docker-compose.import.yml
	de := docker-compose exec
	dr := $(dc) run --rm
	sy := $(de) php bin/console
	node := $(dr) node
	php := $(dr) --no-deps php
else
	sy := php bin/console
	node :=
	php :=
endif

.PHONY: dev
dev: vendor/autoload.php ## Lance le serveur de développement
	$(dc) -f docker-compose-dev.yml up -d

.PHONY: deploy
deploy: ## Déploie une nouvelle version du site
	ssh -A $(server) -l $(login) 'cd $(domain) && git pull origin master && make install'

.PHONY: seed
seed: vendor/autoload.php ## Génère des données dans la base de données (docker-compose up doit être lancé)
	$(sy) doctrine:database:create -q
	$(sy) doctrine:schema:update --force -q
	$(sy) doctrine:schema:validate -q
	$(sy) doctrine:fixtures:load -q
	$(sy) cache:clear -q

.PHONY: watch
watch: vendor/autoload.php ## Migre la base de données (docker-compose up doit être lancé)
	yarn encore dev --watch

.PHONY: lint
lint: vendor/autoload.php ## Analyse le code
	docker run -v $(PWD):/app -w /app -t --rm php:7.4-cli-alpine php -d memory_limit=-1 bin/console lint:container
	docker run -v $(PWD):/app -w /app -t --rm php:7.4-cli-alpine php -d memory_limit=-1 ./vendor/bin/phpstan analyse

.PHONY: install
install: vendor/autoload.php public/assets/manifest.json ## Installe les différentes dépendances
	APP_ENV=prod APP_DEBUG=0 $(php) composer install --no-dev --optimize-autoloader
	APP_ENV=prod APP_DEBUG=0 $(php) bin/console cache:clear
	$(dr) php bin/console doctrine:schema:update --force
	$(dr) php bin/console cache:pool:clear cache.global_clearer
	$(dr) php bin/console messenger:stop-workers
	sudo docker-compose restart php
	sudo docker-compose restart nginx

# -----------------------------------
# Dépendances
# -----------------------------------
vendor/autoload.php: composer.lock
	$(php) composer install
	sudo touch vendor/autoload.php

public/assets/manifest.json: package.json
	$(node) yarn
	$(node) yarn run build
