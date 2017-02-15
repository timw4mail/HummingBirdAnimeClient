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
	
pecl install xdebug

echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)\n" >> /usr/local/etc/php/conf.d/xdebug.ini
#echo "xdebug.remote_enable=on\n" >> /usr/local/etc/php/conf.d/xdebug.ini
#echo "xdebug.remote_autostart=off\n" >> /usr/local/etc/php/conf.d/xdebug.ini
#echo "xdebug.remote_port=9000\n" >> /usr/local/etc/php/conf.d/xdebug.ini

# Install phpunit, the tool that we will use for testing
curl -Lo /usr/local/bin/phpunit https://phar.phpunit.de/phpunit.phar
chmod +x /usr/local/bin/phpunit

# Install extensions
#pecl install xdebug
#echo "zend_extension=/usr/lib/php7/modules/xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini
#docker-php-ext-install xsl
#docker-php-ext-install zip