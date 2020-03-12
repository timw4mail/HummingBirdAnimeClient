<?php declare(strict_types=1);
/**
 * Ion
 *
 * Building blocks for web development
 *
 * PHP version 7.2
 *
 * @package     Ion
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.0.0
 * @link        https://git.timshomepage.net/aviat/ion
 */

namespace Aviat\Ion;

use Aviat\Ion\Exception\ConfigException;
use Aviat\Ion\Type\ArrayType;
use InvalidArgumentException;

/**
 * Wrapper for configuration values
 */
class Config implements ConfigInterface {

	use ArrayWrapper;

	/**
	 * Config object
	 *
	 * @var ArrayType
	 */
	protected $map;

	/**
	 * Constructor
	 *
	 * @param array $configArray
	 */
	public function __construct(array $configArray = [])
	{
		$this->map = $this->arr($configArray);
	}

	/**
	 * Does the config item exist?
	 *
	 * @param string|int|array $key
	 * @return bool
	 */
	public function has($key): bool
	{
		return $this->map->hasKey($key);
	}

	/**
	 * Get a config value
	 *
	 * @param array|string|null $key
	 * @return mixed
	 * @throws ConfigException
	 */
	public function get($key = NULL)
	{
		if (\is_array($key))
		{
			return $this->map->getDeepKey($key);
		}

		return $this->map->get($key);
	}

	/**
	 * Remove a config value
	 *
	 * @param  string|array $key
	 * @return void
	 */
	public function delete($key): void
	{
		if (\is_array($key))
		{
			$this->map->setDeepKey($key, NULL);
		}
		else
		{
			$pos =& $this->map->get($key);
			$pos = NULL;
		}
	}

	/**
	 * Set a config value
	 *
	 * @param integer|string|array $key
	 * @param mixed                $value
	 * @throws InvalidArgumentException
	 * @return ConfigInterface
	 */
	public function set($key, $value): ConfigInterface
	{
		if (\is_array($key))
		{
			$this->map->setDeepKey($key, $value);
		}
		else if (is_scalar($key) && ! empty($key))
		{
			$this->map->set($key, $value);
		}
		else
		{
			throw new InvalidArgumentException('Key must be integer, string, or array, and cannot be empty');
		}

		return $this;
	}
}
// End of config.php