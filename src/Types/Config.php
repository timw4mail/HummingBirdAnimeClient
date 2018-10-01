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
	public $route_config;

	// Settings in config.toml
	public $kitsu_username;
	public $show_anime_collection;
	public $show_manga_collection;
	public $whose_list;

	// Application config
	public $menus;
	public $routes;

	// Generated config values
	public $asset_dir;
	public $base_config_dir;
	public $config_dir;
	public $data_cache_path;
	public $img_cache_path;
	public $view_path;

	public function setAnilist ($data): void
	{
		$this->anilist = new class($data) extends AbstractType {
			public $enabled;

			public $client_id;
			public $client_secret;
			public $redirect_uri;

			public $access_token;
			public $refresh_token;

			public $user_id;
			public $username;
		};
	}

	public function setCache ($data): void
	{
		$this->cache = new class($data) extends AbstractType {
			public $driver;
			public $connection;
			public $options;
		};
	}

	public function setDatabase ($data): void
	{
		$this->database = new class($data) extends AbstractType {
			public $collection;

			public function setCollection ($data): void
			{
				$this->collection = new class($data) extends AbstractType {
					public $type;
					public $host;
					public $user;
					public $pass;
					public $port;
					public $database;
					public $file;
				};
			}
		};
	}

	public function setRoute_config ($data): void
	{
		$this->route_config = new class($data) extends AbstractType {
			public $asset_path;
			public $default_list;
			public $default_anime_list_path;
			public $default_manga_list_path;
		};
	}
}