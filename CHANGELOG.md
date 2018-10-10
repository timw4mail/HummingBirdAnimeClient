# Changelog

## Version 4.1
* Removed MAL integration, added Anilist Integration
* Now uses WebP cache images when the browser supports it
* Replaces JS minifier with pre-minified scripts (Removes the need for one caching folder, too)
* Updated console command to sync Kitsu and Anilist data
* Added page to update settings without having to edit config files

## Version 4
* Updated to use Kitsu API after discontinuation of Hummingbird
* Added streaming links to list entries from the Kitsu API
* Added simple integration with MyAnimeList, so an update can cross-post to both Kitsu and MyAnimeList (anime and manga)
* Added console command to sync Kitsu and MyAnimeList data

* Added character pages 

## Version 3
* Converted user configuration to toml files
* Added a caching layer for api calls, which resets upon updates from the
app.
* Added a bulk thumbnail generator script
* Removed json file "cache" from the app folder

