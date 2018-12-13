<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.1
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

class Config extends AbstractType {
	// Config files/namespaces
	public $anilist;
	public $cache;
	public $database;

	// Settings in config.toml
	public $asset_path; // Path to public folder for urls
	public $dark_theme;
	public $default_anime_list_path;
	public $default_list;
	public $default_manga_list_path;
	public $default_view_type;
	public $kitsu_username;
	public $secure_urls = TRUE;
	public $show_anime_collection;
	public $show_manga_collection;
	public $whose_list;

	// Application config
	public $menus;
	public $routes;

	// Generated config values
	public $asset_dir; // Path to public folder for local files
	public $base_config_dir;
	public $config_dir;
	public $data_cache_path;
	public $img_cache_path;
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