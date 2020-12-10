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
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

class Config extends AbstractType {

	// ------------------------------------------------------------------------
	// Config files/namespaces
	// ------------------------------------------------------------------------

	public ?Config\Anilist $anilist;

	public ?Config\Cache $cache;

	public ?Config\Database $database;

	// ------------------------------------------------------------------------
	// Settings in config.toml
	// ------------------------------------------------------------------------

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
	 */
	public ?string $default_manga_list_path;

	/**
	 * @var 'cover_view' | 'list_view'
	 */
	public ?string $default_view_type;

	public ?string $kitsu_username;

	public bool $secure_urls = TRUE;

	public bool $show_anime_collection = FALSE;

	public bool $show_manga_collection = FALSE;

	/**
	 * CSS theme: light, dark, or auto-switching
	 *
	 * @var 'auto' | 'light' | 'dark'
	 */
	public ?string $theme;

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

	public ?string $asset_dir; // Path to public folder for local files

	public ?string $base_config_dir;

	public ?string $config_dir;

	public ?string $data_cache_path;

	public ?string $img_cache_path;

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