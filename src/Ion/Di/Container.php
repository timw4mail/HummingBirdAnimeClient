<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Di;

use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use Psr\Log\LoggerInterface;

/**
 * Dependency container
 */
class Container implements ContainerInterface {

	/**
	 * Array of container Generator functions
	 *
	 * @var Callable[]
	 */
	protected array $container = [];

	/**
	 * Array of object instances
	 *
	 * @var array
	 */
	protected array $instances = [];

	/**
	 * Map of logger instances
	 *
	 * @var array
	 */
	protected array $loggers = [];

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
	 * @param string $id - Identifier of the entry to look for.
	 *
	 * @throws NotFoundException - No entry was found for this identifier.
	 * @throws ContainerException - Error while retrieving the entry.
	 *
	 * @return mixed Entry.
	 */
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
	 * @param string $id   - Identifier of the entry to look for.
	 * @param array|null $args - Optional arguments for the factory callable
	 * @throws NotFoundException - No entry was found for this identifier.
	 * @throws ContainerException - Error while retrieving the entry.
	 * @return mixed
	 */
	public function getNew(string $id, ?array $args = NULL): mixed
	{
		if ($this->has($id))
		{
			// By default, call a factory with the Container
			$args = \is_array($args) ? $args : [$this];
			$obj = \call_user_func_array($this->container[$id], $args);

			// Check for container interface, and apply the container to the object
			// if applicable
			return $this->applyContainer($obj);
		}

		throw new NotFoundException("Item '{$id}' does not exist in container.");
	}

	/**
	 * Add a factory to the container
	 *
	 * @param string $id
	 * @param Callable  $value - a factory callable for the item
	 * @return ContainerInterface
	 */
	public function set(string $id, Callable $value): ContainerInterface
	{
		$this->container[$id] = $value;
		return $this;
	}

	/**
	 * Set a specific instance in the container for an existing factory
	 *
	 * @param string $id
	 * @param mixed $value
	 * @throws NotFoundException - No entry was found for this identifier.
	 * @return ContainerInterface
	 */
	public function setInstance(string $id, mixed $value): ContainerInterface
	{
		if ( ! $this->has($id))
		{
			throw new NotFoundException("Factory '{$id}' does not exist in container. Set that first.");
		}

		$this->instances[$id] = $value;
		return $this;
	}

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * @param string $id Identifier of the entry to look for.
	 * @return boolean
	 */
	public function has(string $id): bool
	{
		return array_key_exists($id, $this->container);
	}

	/**
	 * Determine whether a logger channel is registered
	 *
	 * @param  string $id The logger channel
	 * @return boolean
	 */
	public function hasLogger(string $id = 'default'): bool
	{
		return array_key_exists($id, $this->loggers);
	}

	/**
	 * Add a logger to the Container
	 *
	 * @param LoggerInterface $logger
	 * @param string          $id     The logger 'channel'
	 * @return ContainerInterface
	 */
	public function setLogger(LoggerInterface $logger, string $id = 'default'): ContainerInterface
	{
		$this->loggers[$id] = $logger;
		return $this;
	}

	/**
	 * Retrieve a logger for the selected channel
	 *
	 * @param  string $id The logger to retrieve
	 * @return LoggerInterface|null
	 */
	public function getLogger(string $id = 'default'): ?LoggerInterface
	{
		return $this->hasLogger($id)
			? $this->loggers[$id]
			: NULL;
	}

	/**
	 * Check if object implements ContainerAwareInterface
	 * or uses ContainerAware trait, and if so, apply the container
	 * to that object
	 *
	 * @param mixed $obj
	 * @return mixed
	 */
	private function applyContainer(mixed $obj): mixed
	{
		$traitName = ContainerAware::class;
		$interfaceName = ContainerAwareInterface::class;

		$traits = class_uses($obj);
		$traitsUsed = (is_array($traits)) ? $traits : [];
		$usesTrait = in_array($traitName, $traitsUsed, TRUE);

		$interfaces = class_implements($obj);
		$implemented = (is_array($interfaces)) ? $interfaces : [];
		$implementsInterface = in_array($interfaceName, $implemented, TRUE);

		if ($usesTrait || $implementsInterface)
		{
			$obj->setContainer($this);
		}

		return $obj;
	}
}
// End of Container.php