# Hummingbird Anime Client

A self-hosted client that allows custom formatting of data from the hummingbird api

[![Build Status](https://travis-ci.org/timw4mail/HummingBirdAnimeClient.svg)](https://travis-ci.org/timw4mail/HummingBirdAnimeClient)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/timw4mail/HummingBirdAnimeClient/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/timw4mail/HummingBirdAnimeClient/?branch=master)

[[Hosted Example](https://anime.timshomepage.net)]

## Features

* Anime List views (Each with list and cover views):
	* Watching
	* Plan to Watch
	* On Hold
	* Dropped
	* Completed 
	* All of the above
	
* Manga List views (Each with list and cover views):
	* Reading
	* Plan to Read
	* On Hold
	* Dropped
	* Completed
	* All of the above
	
* Anime collection view (segmented by media type):
	* Cover Images
	* Table List
	
### Requirements

* PHP 5.5+
* PDO SQLite (For collection tab)
* GD

### Installation

1. Install dependencies via composer: `composer install`
2. Configure settings in `app/config/config.php` to your liking
3. Create the following directories if they don't exist, and make sure they are world writable
	* app/cache
	* public/images/manga
	* public/images/anime
	* public/js/cache

#### Anime Collection Additional Installation
* Run `php /vendor/bin/phinx migrate -e development` to create the database tables
* For importing anime:
	1. Login 
	2. Use the form to select your media 
	3. Save & Repeat as needed
* For bulk importing anime:
	1. Find the anime you are looking for on the hummingbird search api page: `https://hummingbird.me/api/v1/search/anime?query=`
	2. Create an `import.json` file in the root of the app, with an array of objects from the search page that you want to import
	3. Go to the anime collection tab, and the import will be run

 