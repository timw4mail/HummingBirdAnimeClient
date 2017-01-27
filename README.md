# Hummingbird Anime Client

A self-hosted client that allows custom formatting of data from the hummingbird api

[![Build Status](https://travis-ci.org/timw4mail/HummingBirdAnimeClient.svg?branch=master)](https://travis-ci.org/timw4mail/HummingBirdAnimeClient)
[![build status](https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient/badges/develop/build.svg)](https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient/commits/develop)
[![coverage report](https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient/badges/develop/coverage.svg)](https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient/commits/develop)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/timw4mail/HummingBirdAnimeClient/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/timw4mail/HummingBirdAnimeClient/?branch=master)

[[Hosted Example](https://list.timshomepage.net)]

## Features

* Anime List views (Each with list and cover views):
	* Watching
	* Plan to Watch
	* On Hold
	* Dropped
	* Completed
	* Combined View

* Manga List views (Each with list and cover views):
	* Reading
	* Plan to Read
	* On Hold
	* Dropped
	* Completed
	* Combined View

* Anime collection view (segmented by media type):
	* Cover Images
	* Table List

### Requirements

* PHP 7.0+
* PDO SQLite or PDO PostgreSQL (For collection tab)
* GD
* Redis or Memcached for caching

### Installation

1. Install via composer: `composer create-project timw4mail/hummingbird-anime-client`

or

1. Install via git, then install dependencies via composer: `composer install`

2. Duplicate `app/config/*.toml.example` files as `app/config/*.toml`
3. Configure settings in `app/config/config.toml` to your liking
4. Create the following directories if they don't exist, and make sure they are world writable
	* public/js/cache
5. Make sure the `console` script is executable

### Server Setup

#### Caching

Update `app/config/cache.toml` based on the instructions [here](https://git.timshomepage.net/timw4mail/banker/blob/master/README.md).


#### nginx
Basic nginx setup

```nginx
server {
	location / {
		try_files $uri $uri/ /index.php$uri?$args;
	}

	location ~ ^(.+\.php)($|/) {
		fastcgi_split_path_info ^(.+\.php)(.*)$;
		fastcgi_index index.php;
	}

	location ^~ /vendor {
		deny all;
	}
}
```

#### Apache
Make sure to have `mod_rewrite` and `AllowOverride All` enabled in order to take
advantage of the included `.htaccess` file. If you don't wish to use an `.htaccess` file,
include the contents of the `.htaccess` file in your Apache configuration.

#### Anime Collection Additional Installation
* Run `php /vendor/bin/phinx migrate -e development` to create the database tables
* For importing anime:
	1. Login
	2. Use the form to select your media
	3. Save &amp; Repeat as needed
* For bulk importing anime:
	1. Find the anime you are looking for on the hummingbird search api page: `https://hummingbird.me/api/v1/search/anime?query=`
	2. Create an `import.json` file in the root of the app, with an array of objects from the search page that you want to import
	3. Go to the anime collection tab, and the import will be run

