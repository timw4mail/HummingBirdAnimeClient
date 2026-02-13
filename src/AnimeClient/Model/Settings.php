<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\Types\Config;
use Aviat\AnimeClient\Types\UndefinedPropertyException;
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

	public function __construct(
		private readonly ConfigInterface $config,
	) {}

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
						if (! array_key_exists($k, $values[$key] ?? []))
						{
							unset($value['fields'][$k]);

							continue;
						}

						$value['fields'][$k]['disabled'] = false;
						$value['fields'][$k]['display'] = true;
						$value['fields'][$k]['readonly'] = false;
						$value['fields'][$k]['value'] = $values[$key][$k];
					}
				}

				$value['value'] = array_key_exists($key, $values) && is_scalar($values[$key])
					? $values[$key]
					: $value['default'] ?? '';

				foreach (['readonly', 'disabled'] as $flag)
				{
					if (array_key_exists($flag, $value))
					{
						continue;
					}

					$value[$flag] = false;
				}

				if (! array_key_exists('display', $value))
				{
					$value['display'] = true;
				}

				$output[$file][$key] = $value;
			}
		}

		return $output;
	}

	/**
	 * @param array<string, mixed> $settings
	 * @return mixed[]
	 */
	public function validateSettings(array $settings): array
	{
		$cfg = Config::check($settings);
		if (! is_iterable($cfg))
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
				$looseConfig[$key] = match ($val)
				{
					'1' => true,
					'0' => false,
					default => $val,
				};
			}
			elseif (is_array($val) && $val !== [])
			{
				foreach ($val as $k => $v)
				{
					$keyedConfig[$key][$k] = match ($v)
					{
						'1' => true,
						'0' => false,
						default => $v,
					};
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

	/**
	 * @param array<string, mixed> $settings
	 * @return bool
	 */
	public function saveSettingsFile(array $settings): bool
	{
		$configWrapped = count(array_keys($settings)) === 1 && array_key_exists('config', $settings);
		if ($configWrapped)
		{
			$settings = $settings['config'];
		}

		try {
			$settings = $this->validateSettings($settings);
		}
		catch (UndefinedPropertyException $e) {
			return false;
		}

		$savePath = _dir(dirname(__DIR__, 3), 'app', 'config');
		$saveFile = _dir($savePath, 'admin-override.toml');

		$saved = file_put_contents($saveFile, arrayToToml($settings));

		return $saved !== false;
	}
}
