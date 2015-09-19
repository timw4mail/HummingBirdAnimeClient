<?php

namespace Aviat\Ion\Di;

use ArrayObject;

/**
 * Dependency container
 */
class Container implements ContainerInterface {

	/**
	 * Array with class instances
	 *
	 * @var array
	 */
	protected $container = [];

	/**
	 * Constructor
	 *
	 * @param array $values (optional)
	 */
	public function __construct(array $values = [])
	{
		$this->container =  new ArrayObject($values);
	}

	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @throws NotFoundException  No entry was found for this identifier.
	 * @throws ContainerException Error while retrieving the entry.
	 *
	 * @return mixed Entry.
	 */
	public function get($id)
	{
		if ( ! is_string($id))
		{
			throw new Exception\ContainerException("Id must be a string");
		}

		if ($this->has($id))
		{
			return $this->container[$id];
		}

		throw new Exception\NotFoundException("Item '{$id}' does not exist in container.");
	}

	/**
	 * Add a value to the container
	 *
	 * @param string $id
	 * @param mixed $value
	 * @return Container
	 */
	public function set($id, $value)
	{
		$this->container[$id] = $value;
		return $this;
	}

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return boolean
	 */
	public function has($id)
	{
		return $this->container->offsetExists($id);
	}
}
// End of Container.php