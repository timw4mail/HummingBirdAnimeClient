<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

class Config extends AbstractType {

	// ------------------------------------------------------------------------
	// Config files/namespaces
	// ------------------------------------------------------------------------

	/**
	 * @var Config\Anilist
	 */
	public $anilist;

	/**
	 * @var Config\Cache
	 */
	public $cache;

	/**
	 * @var Config\Database
	 */
	public $database;

	// ------------------------------------------------------------------------
	// Settings in config.toml
	// ------------------------------------------------------------------------

	/**
	 * @var string
	 */
	public $asset_path; // Path to public folder for urls

	/**
	 * @deprecated Use 'theme' instead
	 * @var bool
	 */
	public $dark_theme; /* Deprecated */

	/**
	 * Default Anime list status page, values are listed in
	 * Aviat\AnimeClient\API\Enum\AnimeWatchingStatus\Title
	 *
	 * @var string
	 */
	public $default_anime_list_path;

	/**
	 * The list to redirect to from the root url
	 *
	 * @var 'anime' | 'manga'
	 */
	public $default_list;

	/**
	 * Default Manga list status page, values are listed in
	 * Aviat\AnimeClient\API\Enum\MangaReadingStatus\Title
	 *
	 * @var string
	 */
	public $default_manga_list_path;

	/**
	 * @var 'cover_view' | 'list_view'
	 */
	public $default_view_type;

	/**
	 * @var string
	 */
	public $kitsu_username;

	/**
	 * @var bool
	 */
	public $secure_urls = TRUE;

	/**
	 * @var bool
	 */
	public $show_anime_collection;

	/**
	 * @var bool
	 */
	public $show_manga_collection = FALSE;

	/**
	 * CSS theme: light, dark, or auto-switching
	 *
	 * @var 'auto' | 'light' | 'dark'
	 */
	public $theme;

	/**
	 * @var string
	 */
	public $whose_list;

	// ------------------------------------------------------------------------
	// Application config
	// ------------------------------------------------------------------------

	/**
	 * @var array
	 */
	public $menus;

	/**
	 * @var array
	 */
	public $routes;

	// ------------------------------------------------------------------------
	// Generated config values
	// ------------------------------------------------------------------------

	/**
	 * @var string
	 */
	public $asset_dir; // Path to public folder for local files

	/**
	 * @var string
	 */
	public $base_config_dir;

	/**
	 * @var string
	 */
	public $config_dir;

	/**
	 * @var string
	 */
	public $data_cache_path;

	/**
	 * @var string
	 */
	public $img_cache_path;

	/**
	 * @var string
	 */
	public $view_path;

	public function setAnilist ($data): void
	{
		$this->anilist = new Config\Anilist($data);
	}

	public function setCache ($data): void
	{
		$this->cache = new Config\Cache($data);
	}

	public function setDatabase ($data): void
	{
		$this->database = new Config\Database($data);
	}
}