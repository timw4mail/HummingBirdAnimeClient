# Hummingbird Anime Client

Update your anime/manga list on Kitsu.io and MyAnimeList.net

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

1. Install via git, then install dependencies via composer: `composer install`
2. Duplicate `app/config/*.toml.example` files as `app/config/*.toml`
3. Configure settings in `app/config/config.toml` to your liking
4. Create the following directories if they don't exist, and make sure they are world writable
	* public/js/cache
	* public/images/avatars
	* public/images/anime
	* public/images/characters
	* public/images/manga
5. Make sure the `console` script is executable

### Using MAL API
1. Update `app/config/mal.toml` with your username and password
2. Enable MAL api in `app/config/config.toml`

### Server Setup

See the [wiki](https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient/wikis/home)
for more in-depth information

