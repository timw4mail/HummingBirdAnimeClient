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
	unzip \
	libfreetype6 \
	libjpeg62-turbo \
	libmcrypt4 \
	libpng12-0 \
	libfreetype6-dev \
	libjpeg-dev \
	libmcrypt-dev \
	libpng12-dev \
	libxslt1-dev \
	libxslt1.1 \
	zlib1g-dev \
	-yqq

# Install phpunit, the tool that we will use for testing
curl -Lo /usr/local/bin/phpunit https://phar.phpunit.de/phpunit.phar
chmod +x /usr/local/bin/phpunit

# Install gd
docker-php-ext-configure gd --enable-gd-native-ttf --with-jpeg-dir=/usr/lib/x86_64-linux-gnu --with-png-dir=/usr/lib/x86_64-linux-gnu --with-freetype-dir=/usr/lib/x86_64-linux-gnu
docker-php-ext-install gd
docker-php-ext-install mcrypt
docker-php-ext-install xsl
docker-php-ext-install zip