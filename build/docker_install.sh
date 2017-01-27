#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && [[ ! -e /.dockerinit ]] && exit 0

# Where am I?
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

set -xe

# Install git (the php image doesn't have it) which is required by composer
apt-get update -yqq
apt-get install \
	git \
	libxslt1-dev \
	libxslt1.1 \
	zlib1g-dev \
	unzip \
	-yqq

# Install phpunit, the tool that we will use for testing
curl -Lo /usr/local/bin/phpunit https://phar.phpunit.de/phpunit.phar
chmod +x /usr/local/bin/phpunit

# Install extensions
pecl install xdebug
echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini
docker-php-ext-install xsl
docker-php-ext-install zip