#!/usr/bin/env bash

php /scripts/wait_for_db.php

# To run these manually use "docker-compose exec php bash"
# create database, apply schema and insert data fixtures
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:schema:update --force
php bin/console --env=dev doctrine:fixtures:load
