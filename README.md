# Hummingbird Anime Client

Update your anime/manga list on Kitsu.io and Anilist

[![Build Status](https://travis-ci.com/timw4mail/HummingBirdAnimeClient.svg?branch=master)](https://travis-ci.com/github/timw4mail/HummingBirdAnimeClient)
[![Build Status](https://jenkins.timshome.page/buildStatus/icon?job=timw4mail/HummingBirdAnimeClient/develop)](https://jenkins.timshome.page/job/timw4mail/job/HummingBirdAnimeClient/job/develop/)

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

* PHP 8.2
  * ext-dom (For editing the DOM)
  * ext-gd (For caching images)
  * ext-intl (For time localization)
  * ext-json
  * ext-mbstring
  * ext-pdo

### Highly Recommended

* Redis or Memcached for caching
* PDO SQLite or PDO PostgreSQL (For collection tab)

### Installation

1. Install via git, then install dependencies via composer: `composer install`
2. Duplicate `app/config/config.toml.example` file as `app/config/config.toml`
3. Configure settings in `app/config/config.toml` to your liking
4. Create the following directories if they don't exist, and make sure they are world writable
	* app/config
	* app/logs
	* public/images/avatars
	* public/images/anime
	* public/images/characters
	* public/images/manga
5. Make sure the `console` script is executable
6. Additional settings are on the settings page once you log in.

### Server Setup

See the [wiki](https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient/wiki)
for more in-depth information

