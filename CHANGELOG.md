# Changelog

## Version 5.2
* Updated PHP requirement to 8.1
* Updated to support PHP 8.2
* Improve Anilist <-> Kitsu mappings to be more reliable

## Version 5.1
* Added session check, so when coming back to a page, if the session is expired, the page will refresh.
* Updated logging config so that much fewer, much smaller files are generated.
* Updated Kitsu integration to use GraphQL API, reducing a lot of internal complexity.

## Version 5
* Updated PHP requirement to 7.4
* Added anime watching history view
* Added manga reading history view
* Updated anime collection to have more media types

## Version 4.2
* Updated dependencies
* Updated PHP requirement to 7.3
* Added option to automatically set dark mode based on the OS  setting

## Version 4.1
* Added optional dark theme
* Removed MAL integration, added Anilist Integration
* Now uses WebP cache images when the browser supports it
* Replaces JS minifier with pre-minified scripts (Removes the need for one caching folder, too)
* Updated console command to sync Kitsu and Anilist data (Kitsu can sync MAL, and MAL's API broke, so MAL sync was removed)
* Added page to update settings without having to edit config files
* Defaulted to secure (HTTPS) urls
* Updated Character pages to show voice actors
* Added People pages, showing which works they contributed to, and in what role

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

