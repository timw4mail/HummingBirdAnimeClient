<?php
/**
 * Ion
 *
 * Building blocks for web development
 *
 * @package     Ion
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @license     MIT
 */

namespace Aviat\Ion\Di;

use Psr\Log\LoggerInterface;

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
	 * Map of logger instances
	 *
	 * @var array
	 */
	protected $loggers = [];

	/**
	 * Constructor
	 *
	 * @param array $values (optional)
	 */
	public function __construct(array $values = [])
	{
		$this->container = $values;
		$this->loggers = [];
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
	 * @return ContainerInterface
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
		return array_key_exists($id, $this->container);
	}

	/**
	 * Determine whether a logger channel is registered
	 * @param  string  $key The logger channel
	 * @return boolean
	 */
	public function hasLogger($key = 'default')
	{
		return array_key_exists($key, $this->loggers);
	}

	/**
	 * Add a logger to the Container
	 *
	 * @param LoggerInterface $logger
	 * @param string          $key    The logger 'channel'
	 * @return ContainerInterface
	 */
	public function setLogger(LoggerInterface $logger, $key = 'default')
	{
		$this->loggers[$key] = $logger;
		return $this;
	}

	/**
	 * Retrieve a logger for the selected channel
	 *
	 * @param  string $key The logger to retreive
	 * @return LoggerInterface|null
	 */
	public function getLogger($key = 'default')
	{
		return ($this->hasLogger($key))
			? $this->loggers[$key]
			: NULL;
	}
}
// End of Container.php