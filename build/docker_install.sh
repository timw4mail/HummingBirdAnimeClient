#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && [[ ! -e /.dockerinit ]] && exit 0

set -xe

# Install git (the php image doesn't have it) which is required by composer
# And for some reason apt-utils is not installed
apt-get update -yqq
apt-get install apt-utils
apt-get install git sqlite3 -yqq

# Install phpunit, the tool that we will use for testing
curl -Lo /usr/local/bin/phpunit https://phar.phpunit.de/phpunit.phar
chmod +x /usr/local/bin/phpunit

# Install mysql driver
# Here you can install any other extension that you need
docker-php-ext-install pdo_sqlite
pecl install redis
docker-php-ext-enable redis