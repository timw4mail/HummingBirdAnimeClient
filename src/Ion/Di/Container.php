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

namespace Aviat\Ion\Di;

use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;
use Psr\Log\LoggerInterface;

/**
 * Dependency container
 */
class Container implements ContainerInterface
{
	/**
	 * Constructor
	 *
	 * @param array<string, callable> $container
	 * @param array<string,mixed> $instances
	 * @param array<string, LoggerInterface> $loggers
	 * @param array<string,string> $classIdMap
	 */
	public function __construct(
		/**
		 * Array of container Generator functions
		 *
		 */
		protected array $container = [],

		/**
		 * Array of object instances
		 *
		 */
		protected array $instances = [],

		/**
		 * Map of logger instances
		 *
		 */
		protected array $loggers = [],

		/**
		 * Map classes back to container ids, to make automatic
		 * sub-dependency setup possible
		 *
		 */
		private array $classIdMap = [],
	) {}

	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string $id - Identifier of the entry to look for.
	 *
	 * @throws ContainerException - Error while retrieving the entry.
	 * @throws NotFoundException - No entry was found for this identifier.
	 *
	 * @return mixed Entry.
	 */
	#[\Override]
	public function get(string $id): mixed
	{
		if ($this->has($id))
		{
			// Return an object instance, if it already exists
			if (array_key_exists($id, $this->instances))
			{
				return $this->instances[$id];
			}

			// If there isn't already an instance, create one
			$obj = $this->getNew($id);
			$this->instances[$id] = $obj;

			return $obj;
		}

		throw new NotFoundException("Item '{$id}' does not exist in container.");
	}

	/**
	 * Get a new instance of the specified item
	 *
	 * @param string $id - Identifier or className of the entry to look for.
	 * @param array<mixed>|null $args - Optional arguments for the factory callable
	 * @throws ContainerException - Error while retrieving the entry.
	 * @throws NotFoundException - No entry was found for this identifier.
	 */
	#[\Override]
	public function getNew(string $id, null|array $args = null): mixed
	{
		if ($this->has($id))
		{
			if (array_key_exists($id, $this->classIdMap))
			{
				$id = $this->classIdMap[$id];
			}

			// By default, call a factory with the Container
			$args = \is_array($args) ? $args : [$this];
			$obj = $this->container[$id](...$args);

			// Check for container interface, and apply the container to the object
			// if applicable
			return $this->applyContainer($obj);
		}

		throw new NotFoundException("Item '{$id}' does not exist in container.");
	}

	/**
	 * Add a factory to the container
	 *
	 * @param callable $value - a factory callable for the item
	 */
	#[\Override]
	public function set(string $id, callable $value): ContainerInterface
	{
		$this->container[$id] = $value;

		return $this;
	}

	/**
	 * Add a common simple factory to the container
	 */
	#[\Override]
	public function setSimple(string $id, string $className): ContainerInterface
	{
		$this->classIdMap[$className] = $id;

		return $this->set($id, static fn (ContainerInterface $container) => new $className($container));
	}

	/**
	 * Set a specific instance in the container for an existing factory
	 *
	 * @throws NotFoundException - No entry was found for this identifier.
	 */
	#[\Override]
	public function setInstance(string $id, mixed $value): ContainerInterface
	{
		if (! $this->has($id))
		{
			throw new NotFoundException("Factory '{$id}' does not exist in container. Set that first.");
		}

		$className = $value::class;
		if (! array_key_exists((string) $className, $this->classIdMap))
		{
			$this->classIdMap[$value::class] = $id;
		}

		$this->instances[$id] = $value;

		return $this;
	}

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * @param string $id Identifier of the entry to look for.
	 */
	#[\Override]
	public function has(string $id): bool
	{
		return array_key_exists($id, $this->container) || array_key_exists($id, $this->classIdMap);
	}

	/**
	 * Determine whether a logger channel is registered
	 *
	 * @param string $id The logger channel
	 */
	#[\Override]
	public function hasLogger(string $id = 'default'): bool
	{
		return array_key_exists($id, $this->loggers);
	}

	/**
	 * Add a logger to the Container
	 *
	 * @param string $id The logger 'channel'
	 */
	#[\Override]
	public function setLogger(LoggerInterface $logger, string $id = 'default'): ContainerInterface
	{
		$this->loggers[$id] = $logger;

		return $this;
	}

	/**
	 * Remove an item from the container
	 */
	public function delete(string $id): void
	{
		unset($this->container[$id], $this->instances[$id]);
	}

	/**
	 * Remove a cached instance from the container
	 */
	public function clearInstance(string $id): void
	{
		unset($this->instances[$id]);
	}

	/**
	 * Retrieve a logger for the selected channel
	 *
	 * @param string $id The logger to retrieve
	 */
	#[\Override]
	public function getLogger(string $id = 'default'): null|LoggerInterface
	{
		return $this->hasLogger($id)
			? $this->loggers[$id]
			: null;
	}

	/**
	 * Check if object implements ContainerAwareInterface
	 * or uses ContainerAware trait, and if so, apply the container
	 * to that object
	 */
	private function applyContainer(mixed $obj): mixed
	{
		$traitName = ContainerAware::class;
		$interfaceName = ContainerAwareInterface::class;

		$traits = class_uses($obj);
		$traitsUsed = is_array($traits) ? $traits : [];
		$usesTrait = in_array($traitName, $traitsUsed, true);

		$interfaces = class_implements($obj);
		$implemented = is_array($interfaces) ? $interfaces : [];
		$implementsInterface = in_array($interfaceName, $implemented, true);

		if ($usesTrait || $implementsInterface)
		{
			$obj->setContainer($this);
		}

		return $obj;
	}
}

// End of Container.php
