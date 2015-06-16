# Hummingbird Anime Client

A self-hosted client that allows custom formatting of data from the hummingbird api

[[Hosted Example](https://anime.timshomepage.net)]

## Features

* Anime List views:
	* Watching
	* Plan to Watch
	* On Hold
	* Dropped
	* Completed 
	* All of the above
	
* Manga List views:
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

* PHP 5.4+
* PDO SQLite (For collection tab)

### Installation

1. Install dependencies via composer: `composer install`
2. Change the `WHOSE` constant declaration in `index.php` to your name
3. Configure settings in `app/config/config.php` to your liking
 