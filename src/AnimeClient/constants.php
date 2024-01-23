<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

const DEFAULT_CONTROLLER = Controller\Misc::class;
const DEFAULT_CONTROLLER_METHOD = 'index';
const DEFAULT_CONTROLLER_NAMESPACE = Controller::class;
const DEFAULT_LIST_CONTROLLER = Controller\Anime::class;
const ERROR_MESSAGE_METHOD = 'errorPage';
const NOT_FOUND_METHOD = 'notFound';
const SESSION_SEGMENT = 'Aviat\AnimeClient\Auth';
const SRC_DIR = __DIR__;
const USER_AGENT = "Tim's Anime Client/5.2";

// Regex patterns
const ALPHA_SLUG_PATTERN = '[a-zA-Z_]+';
const NUM_PATTERN = '[0-9]+';
/**
 * Eugh...url slugs can have weird characters
 * So...if it's not a forward slash, sure it's valid 😅
 */
const SLUG_PATTERN = '[^\/]+';

// Why doesn't this already exist?
const MILLI_FROM_NANO = 1000 * 1000;

/**
 * Map config settings to form fields
 */
const SETTINGS_MAP = [
	'anilist' => [
		'enabled' => [
			'type' => 'boolean',
			'title' => 'Enable Anilist Integration',
			'default' => FALSE,
			'description' => 'Enable syncing data between Kitsu and Anilist. Requires appropriate API keys to be set in config',
		],
		'client_id' => [
			'type' => 'string',
			'title' => 'Anilist API Client ID',
			'default' => '',
			'description' => 'The client id for your Anilist API application',
		],
		'client_secret' => [
			'type' => 'string',
			'title' => 'Anilist API Client Secret',
			'default' => '',
			'description' => 'The client secret for your Anilist API application',
		],
		'username' => [
			'type' => 'string',
			'title' => 'Anilist Username',
			'default' => '',
			'description' => 'Login username for Anilist account to integrate with',
		],
		'access_token' => [
			'type' => 'hidden',
			'title' => 'API Access Token',
			'default' => '',
			'description' => 'The Access code for accessing the Anilist API',
			'readonly' => TRUE,
		],
		'access_token_expires' => [
			'type' => 'string',
			'title' => 'Expiration timestamp of the access token',
			'default' => '0',
			'description' => 'The unix timestamp of when the access token expires.',
			'readonly' => TRUE,
		],
		'refresh_token' => [
			'type' => 'string',
			'title' => 'API Refresh Token',
			'default' => '',
			'description' => 'Token to refresh the access token before it expires',
			'readonly' => TRUE,
		],
	],

	'cache' => [
		'driver' => [
			'type' => 'select',
			'title' => 'Cache Type',
			'description' => 'The Cache backend',
			'options' => [
				'Memcached' => 'memcached',
				'Redis' => 'redis',
				'No Cache' => 'null',
			],
		],
		'connection' => [
			'type' => 'subfield',
			'title' => 'Connection',
			'fields' => [
				'host' => [
					'type' => 'string',
					'title' => 'Cache Host',
					'description' => 'Host of the cache backend to connect to',
				],
				'port' => [
					'type' => 'string',
					'title' => 'Cache Port',
					'description' => 'Port of the cache backend to connect to',
					'default' => NULL,
				],
				'password' => [
					'type' => 'string',
					'title' => 'Cache Password',
					'description' => 'Password to connect to cache backend',
					'default' => NULL,
				],
				'persistent' => [
					'type' => 'boolean',
					'title' => 'Persistent Cache Connection',
					'description' => 'Whether to have a persistent connection to the cache',
					'default' => FALSE,
				],
				'database' => [
					'type' => 'string',
					'title' => 'Cache Database',
					'default' => '1',
					'description' => 'Cache database number for Redis',
				],
			],
		],
	],
	'config' => [
		'kitsu_username' => [
			'type' => 'string',
			'title' => 'Kitsu Username',
			'default' => '',
			'description' => 'Username of the account to pull list data from.',
		],
		'whose_list' => [
			'type' => 'string',
			'title' => 'Whose List',
			'default' => 'Somebody',
			'description' => 'Name of the owner of the list data.',
		],
		'timezone' => [
			'type' => 'string',
			'title' => 'Timezone',
			'default' => 'America/Detroit',
			'description' => 'See https://www.php.net/manual/en/timezones.php for options',
		],
		'theme' => [
			'type' => 'select',
			'title' => 'Theme',
			'default' => 'auto',
			'description' => 'Which color scheme to use?',
			'options' => [
				'Automatically match OS theme' => 'auto',
				'Original Light Theme' => 'light',
				'Dark Theme' => 'dark',
			],
		],
		'show_anime_collection' => [
			'type' => 'boolean',
			'title' => 'Show Anime Collection',
			'default' => FALSE,
			'description' => 'Should the anime collection be shown?',
		],
		'show_manga_collection' => [
			'type' => 'boolean',
			'title' => 'Show Manga Collection',
			'default' => FALSE,
			'description' => 'Should the manga collection be shown?',
		],
		'default_list' => [
			'type' => 'select',
			'title' => 'Default List',
			'description' => 'Which list to show by default.',
			'options' => [
				'Anime' => 'anime',
				'Manga' => 'manga',
			],
		],
		'default_anime_list_path' => [ //watching|plan_to_watch|on_hold|dropped|completed|all
			'type' => 'select',
			'title' => 'Default Anime List Section',
			'description' => 'Which part of the anime list to show by default.',
			'options' => [
				'Watching' => 'watching',
				'Plan to Watch' => 'plan_to_watch',
				'On Hold' => 'on_hold',
				'Dropped' => 'dropped',
				'Completed' => 'completed',
				'All' => 'all',
			],
		],
		'default_manga_list_path' => [ //reading|plan_to_read|on_hold|dropped|completed|all
			'type' => 'select',
			'title' => 'Default Manga List Section',
			'description' => 'Which part of the manga list to show by default.',
			'options' => [
				'Reading' => 'reading',
				'Plan to Read' => 'plan_to_read',
				'On Hold' => 'on_hold',
				'Dropped' => 'dropped',
				'Completed' => 'completed',
				'All' => 'all',
			],
		],
	],
	'database' => [
		'type' => [
			'type' => 'select',
			'title' => 'Database Type',
			'options' => [
				'MySQL' => 'mysql',
				'PostgreSQL' => 'pgsql',
				'SQLite' => 'sqlite',
			],
			'default' => 'sqlite',
			'description' => 'Type of database to connect to',
		],
		'host' => [
			'type' => 'string',
			'title' => 'Host',
			'description' => 'The host of the database server',
		],
		'user' => [
			'type' => 'string',
			'title' => 'User',
			'description' => 'Database connection user',
		],
		'pass' => [
			'type' => 'string',
			'title' => 'Password',
			'description' => 'Database connection password',
		],
		'port' => [
			'type' => 'string',
			'title' => 'Port',
			'description' => 'Database connection port',
			'default' => NULL,
		],
		'database' => [
			'type' => 'string',
			'title' => 'Database Name',
			'description' => 'Name of the database/schema to connect to',
		],
		'file' => [
			'type' => 'string',
			'title' => 'Database File',
			'description' => 'Path to the database file, if required by the current database type.',
			'default' => 'anime_collection.sqlite',
		],
	],
];
