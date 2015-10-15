<?php
/**
 * Base Configuration class
 */

namespace Aviat\AnimeClient;

/**
 * Wrapper for configuration values
 */
class Config {

	use \Aviat\Ion\ArrayWrapper;

	/**
	 * Config object
	 *
	 * @var array
	 */
	protected $map = [];

	/**
	 * Constructor
	 *
	 * @param array $config_array
	 */
	public function __construct(array $config_array = [])
	{
		$this->map = $config_array;
	}

	/**
	 * Get a config value
	 *
	 * @param array|string $key
	 * @return mixed
	 */
	public function get($key)
	{
		if (is_array($key))
		{
			return $this->get_deep_key($key, FALSE);
		}

		if (array_key_exists($key, $this->map))
		{
			return $this->map[$key];
		}

		return NULL;
	}

	/**
	 * Return a reference to an arbitrary key on the config map
	 * @param  array $key
	 * @param bool $create Whether to create the missing array keys
	 * @return mixed
	 */
	protected function &get_deep_key(array $key, $create = TRUE)
	{
		$pos =& $this->map;

		// Create the start of the array if it doesn't exist
		if ($create &&  ! is_array($pos))
		{
			$pos = [];
		}
		elseif ( ! is_array($pos))
		{
			return NULL;
		}

		// Iterate through the levels of the array,
		// create the levels if they don't exist
		foreach($key as $level)
		{
			if ($create && empty($pos) && ! is_array($pos))
			{
				$pos = [];
				$pos[$level] = [];
			}
			$pos =& $pos[$level];
		}

		return $pos;
	}

	/**
	 * Remove a config value
	 *
	 * @param  string|array $key
	 * @return void
	 */
	public function delete($key)
	{
		$pos =& $this->map;

		if (is_array($key))
		{
			$pos =& $this->arr($this->map)->get_deep_key($key);
		}
		else
		{
			$pos =& $this->map[$key];
		}

		unset($pos);
	}

	/**
	 * Set a config value
	 *
	 * @param string|array $key
	 * @param mixed $value
	 * @return Config
	 */
	public function set($key, $value)
	{
		$pos =& $this->map;

		if (is_array($key))
		{
			$pos =& $this->get_deep_key($key);
			$pos = $value;
		}
		else
		{
			$pos[$key] = $value;
		}

		return $this;
	}
}
// End of config.php