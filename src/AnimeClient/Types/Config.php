<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
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
	public ?Config\Anilist $anilist;

	/**
	 * @var Config\Cache
	 */
	public ?Config\Cache $cache;

	/**
	 * @var Config\Database
	 */
	public ?Config\Database $database;

	// ------------------------------------------------------------------------
	// Settings in config.toml
	// ------------------------------------------------------------------------

	/**
	 * @var string
	 */
	public ?string $asset_path; // Path to public folder for urls

	/**
	 * @deprecated Use 'theme' instead
	 * @var bool
	 */
	public $dark_theme; /* Deprecated */

	/**
	 * @var string The PHP timezone
	 */
	public string $timezone = '';

	/**
	 * Default Anime list status page, values are listed in
	 * Aviat\AnimeClient\API\Enum\AnimeWatchingStatus\Title
	 *
	 * @var string
	 */
	public ?string $default_anime_list_path;

	/**
	 * The list to redirect to from the root url
	 *
	 * @var 'anime' | 'manga'
	 */
	public ?string $default_list;

	/**
	 * Default Manga list status page, values are listed in
	 * Aviat\AnimeClient\API\Enum\MangaReadingStatus\Title
	 *
	 * @var string
	 */
	public ?string $default_manga_list_path;

	/**
	 * @var 'cover_view' | 'list_view'
	 */
	public ?string $default_view_type;

	/**
	 * @var string
	 */
	public ?string $kitsu_username;

	/**
	 * @var bool
	 */
	public bool $secure_urls = TRUE;

	/**
	 * @var bool
	 */
	public $show_anime_collection = FALSE;

	/**
	 * @var bool
	 */
	public $show_manga_collection = FALSE;

	/**
	 * CSS theme: light, dark, or auto-switching
	 *
	 * @var 'auto' | 'light' | 'dark'
	 */
	public ?string $theme;

	/**
	 * @var string
	 */
	public ?string $whose_list;

	// ------------------------------------------------------------------------
	// Application config
	// ------------------------------------------------------------------------

	/**
	 * @var array
	 */
	public array $menus;

	/**
	 * @var array
	 */
	public array $routes;

	// ------------------------------------------------------------------------
	// Generated config values
	// ------------------------------------------------------------------------

	/**
	 * @var string
	 */
	public ?string $asset_dir; // Path to public folder for local files

	/**
	 * @var string
	 */
	public ?string $base_config_dir;

	/**
	 * @var string
	 */
	public ?string $config_dir;

	/**
	 * @var string
	 */
	public ?string $data_cache_path;

	/**
	 * @var string
	 */
	public ?string $img_cache_path;

	/**
	 * @var string
	 */
	public ?string $view_path;

	public function setAnilist ($data): void
	{
		$this->anilist = Config\Anilist::from($data);
	}

	public function setCache ($data): void
	{
		$this->cache = Config\Cache::from($data);
	}

	public function setDatabase ($data): void
	{
		$this->database = Config\Database::from($data);
	}
}