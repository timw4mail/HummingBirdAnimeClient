<?php

namespace AnimeClient\Base;

/**
 * Wrapper of Aura container to be in the anime client namespace
 */
class Container {

	/**
	 * @var array
	 */
	protected $container = [];

	/**
	 * Constructor
	 */
	public function __construct(array $values = [])
	{
		$this->container = $values;
	}

	/**
	 * Get a value
	 *
	 * @param string $key
	 * @retun mixed
	 */
	public function get($key)
	{
		if (array_key_exists($key, $this->container))
		{
			return $this->container[$key];
		}
	}

	/**
	 * Add a value to the container
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return Container
	 */
	public function set($key, $value)
	{
		$this->container[$key] = $value;
		return $this;
	}
}
// End of Container.php