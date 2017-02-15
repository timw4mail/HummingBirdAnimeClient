#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && [[ ! -e /.dockerinit ]] && exit 0

set -xe

# Install git (the php image doesn't have it) which is required by composer
apk upgrade --update && apk add \
	curl \
	git \
	libxslt-dev \
	zlib-dev
	
apk add php7-xdebug --repository http://dl-3.alpinelinux.org/alpine/edge/testing/

# Install phpunit, the tool that we will use for testing
curl -Lo /usr/local/bin/phpunit https://phar.phpunit.de/phpunit.phar
chmod +x /usr/local/bin/phpunit

# Install extensions
#pecl install xdebug
echo "zend_extension=/usr/lib/php7/modules/xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini
#docker-php-ext-install xsl
#docker-php-ext-install zip