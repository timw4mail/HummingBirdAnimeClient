<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\Types\{Config, UndefinedPropertyException};

use Aviat\Ion\ConfigInterface;
use Aviat\Ion\Di\ContainerAware;

use function Aviat\AnimeClient\arrayToToml;

use function Aviat\Ion\_dir;
use const Aviat\AnimeClient\SETTINGS_MAP;

/**
 * Model for handling settings control panel
 */
final class Settings
{
	use ContainerAware;

	public function __construct(private ConfigInterface $config)
	{
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getSettings(): array
	{
		$settings = [
			'config' => [],
		];

		foreach (SETTINGS_MAP as $file => $values)
		{
			if ($file === 'config')
			{
				$keys = array_keys($values);

				foreach ($keys as $key)
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

	/**
	 * @return array<mixed, array<string, array<string[]|array<string, mixed>[]|class-string<\memcached>[]|class-string<\redis>[]|bool|float|int|string|null>>>
	 */
	public function getSettingsForm(): array
	{
		$output = [];

		foreach ($this->getSettings() as $file => $values)
		{
			$values ??= [];

			foreach (SETTINGS_MAP[$file] as $key => $value)
			{
				if ($value['type'] === 'subfield')
				{
					foreach ($value['fields'] as $k => $field)
					{
						if (empty($values[$key][$k]))
						{
							unset($value['fields'][$k]);

							continue;
						}

						$value['fields'][$k]['disabled'] = FALSE;
						$value['fields'][$k]['display'] = TRUE;
						$value['fields'][$k]['readonly'] = FALSE;
						$value['fields'][$k]['value'] = $values[$key][$k] ?? '';
					}
				}

				if (array_key_exists($key, $values) && is_scalar($values[$key]))
				{
					$value['value'] = $values[$key];
				}
				else
				{
					$value['value'] = $value['default'] ?? '';
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

	/**
	 * @return mixed[]
	 */
	public function validateSettings(array $settings): array
	{
		$cfg = Config::check($settings);
		if ( ! is_iterable($cfg))
		{
			return [];
		}

		$looseConfig = [];
		$keyedConfig = [];

		// Convert 'boolean' values to true and false
		// Also order keys so they can be saved properly
		foreach ($cfg as $key => $val)
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
			elseif (is_array($val) && ! empty($val))
			{
				foreach ($val as $k => $v)
				{
					if ($v === '1')
					{
						$keyedConfig[$key][$k] = TRUE;
					}
					elseif ($v === '0')
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

		foreach ($looseConfig as $k => $v)
		{
			$output[$k] = $v;
		}

		foreach ($keyedConfig as $k => $v)
		{
			$output[$k] = $v;
		}

		return $output;
	}

	public function saveSettingsFile(array $settings): bool
	{
		$configWrapped = (count(array_keys($settings)) === 1 && array_key_exists('config', $settings));
		if ($configWrapped)
		{
			$settings = $settings['config'];
		}

		try
		{
			$settings = $this->validateSettings($settings);
		}
		catch (UndefinedPropertyException $e)
		{
			dump($e);
			dump($settings);

			return FALSE;
		}

		$savePath = _dir(dirname(__DIR__, 3), 'app', 'config');
		$saveFile = _dir($savePath, 'admin-override.toml');

		$saved = file_put_contents($saveFile, arrayToToml($settings));

		return $saved !== FALSE;
	}
}
