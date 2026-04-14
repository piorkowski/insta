#!/bin/bash

set -e

[ -f .env ] || cp .env.dist .env
[ -f .env.test ] || cp .env.test.dist .env.test

composer update

php -S 0.0.0.0:8000 -t public public/router.php
