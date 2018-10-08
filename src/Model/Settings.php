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

namespace Aviat\AnimeClient\Model;

use function Aviat\AnimeClient\arrayToToml;
use function Aviat\Ion\_dir;

use Aviat\AnimeClient\Types\{Config, UndefinedPropertyException};

use Aviat\Ion\ConfigInterface;
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\StringWrapper;

/**
 * Model for handling settings control panel
 */
final class Settings {
	use ContainerAware;
	use StringWrapper;

	private $config;

	/**
	 * Map the config values to types and form fields
	 */
	private const SETTINGS_MAP = [
		'anilist' => [
			'enabled' => [
				'type' => 'boolean',
				'title' => 'Enable Anilist Integration',
				'default' => FALSE,
				'description' => 'Enable syncing data between Kitsu and Anilist. Requires appropriate API keys to be set in config',
			],
		],
		'config' => [
			'kitsu_username' => [
				'type' => 'string',
				'title' => 'Kitsu Username',
				'default' => '',
				'readonly' => TRUE,
				'description' => 'Username of the account to pull list data from.',
			],
			'whose_list' => [
				'type' => 'string',
				'title' => 'Whose List',
				'default' => 'Somebody',
				'readonly' => TRUE,
				'description' => 'Name of the owner of the list data.',
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
			'asset_path' => [
				'type' => 'string',
				'display' => FALSE,
				'description' => 'Path to public directory, where images/css/javascript are located',
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
				]
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
				]
			]
		],
		'cache' => [
			'driver' => [
				'type' => 'select',
				'title' => 'Cache Type',
				'description' => 'The Cache backend',
				'options' => [
					'APCu' => 'apcu',
					'Memcached' => 'memcached',
					'Redis' => 'redis',
					'No Cache' => 'null'
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
					],
					'password' => [
						'type' => 'string',
						'title' => 'Cache Password',
						'description' => 'Password to connect to cache backend',
					],
					'database' => [
						'type' => 'string',
						'title' => 'Cache Database',
						'description' => 'Cache database number for Redis',
					],
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
				'description' => 'Database connection password'
			],
			'port' => [
				'type' => 'string',
				'title' => 'Port',
				'description' => 'Database connection port'
			],
			'database' => [
				'type' => 'string',
				'title' => 'Database Name',
				'description' => 'Name of the database/schema to connect to',
			],
			'file' => [
				'type' => 'string',
				'title' => 'Database File',
				'description' => 'Path to the database file, if required by the current database type.'
			],
		],
	];

	public function __construct(ConfigInterface $config)
	{
		$this->config = $config;
	}

	public function getSettings()
	{
		$settings = [
			'config' => [],
		];

		foreach(static::SETTINGS_MAP as $file => $values)
		{
			if ($file === 'config')
			{
				$keys = array_keys($values);
				foreach($keys as $key)
				{
					$settings['config'][$key] = $this->config->get($key);
				}
			}
			else
			{
				$settings[$file] = $this->config->get($file);
			}
		}

		return $settings;
	}

	public function getSettingsForm()
	{
		$output = [];

		$settings = $this->getSettings();

		foreach($settings as $file => $values)
		{
			foreach(static::SETTINGS_MAP[$file] as $key => $value)
			{
				if ($value['type'] === 'subfield')
				{
					foreach($value['fields'] as $k => $field)
					{
						$value['fields'][$k]['value'] = $values[$key][$k] ?? '';
						$value['fields'][$k]['display'] = TRUE;
						$value['fields'][$k]['readonly'] = FALSE;
						$value['fields'][$k]['disabled'] = FALSE;
					}
				}

				if (is_scalar($values[$key]))
				{
					$value['value'] = $values[$key];
				}

				foreach (['readonly', 'disabled'] as $flag)
				{
					if ( ! array_key_exists($flag, $value))
					{
						$value[$flag] = FALSE;
					}
				}

				if ( ! array_key_exists('display', $value))
				{
					$value['display'] = TRUE;
				}

				$output[$file][$key] = $value;
			}
		}

		return $output;
	}

	public function validateSettings(array $settings)
	{
		$config = (new Config($settings))->toArray();

		$looseConfig = [];
		$keyedConfig = [];

		// Convert 'boolean' values to true and false
		// Also order keys so they can be saved properly
		foreach ($config as $key => $val)
		{
			if (is_scalar($val))
			{
				if ($val === '1')
				{
					$looseConfig[$key] = TRUE;
				}
				elseif ($val === '0')
				{
					$looseConfig[$key] = FALSE;
				}
				else
				{
					$looseConfig[$key] = $val;
				}
			}
			elseif (is_array($val))
			{
				foreach($val as $k => $v)
				{
					if ($v === '1')
					{
						$keyedConfig[$key][$k] = TRUE;
					}
					elseif($v === '0')
					{
						$keyedConfig[$key][$k] = FALSE;
					}
					else
					{
						$keyedConfig[$key][$k] = $v;
					}
				}
			}
		}

		ksort($looseConfig);
		ksort($keyedConfig);

		$output = [];

		foreach($looseConfig as $k => $v)
		{
			$output[$k] = $v;
		}

		foreach($keyedConfig as $k => $v)
		{
			$output[$k] = $v;
		}

		return $output;
	}

	public function saveSettingsFile(array $settings): bool
	{
		$settings = $settings['config'];

		try
		{
			$settings = $this->validateSettings($settings);
		}
		catch (UndefinedPropertyException $e)
		{
			return FALSE;
		}

		$savePath = realpath(_dir(__DIR__, '..', '..', 'app', 'config'));
		$saveFile = _dir($savePath, 'admin-override.toml');

		$saved = file_put_contents($saveFile, arrayToToml($settings));

		return $saved !== FALSE;
	}
}