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

namespace Aviat\Ion\Di;

use Psr\Log\LoggerInterface;

/**
 * Interface for the Dependency Injection Container
 *
 * Based on container-interop interface, but return types and
 * scalar type hints make the interface incompatible to the PHP parser
 *
 * @see https://github.com/container-interop/container-interop
 */
interface ContainerInterface {

	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string $id Identifier of the entry to look for.
	 * @throws Exception\NotFoundException  No entry was found for this identifier.
	 * @throws Exception\ContainerException Error while retrieving the entry.
	 * @return mixed Entry.
	 */
	public function get($id);

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * @param string $id Identifier of the entry to look for.
	 * @return boolean
	 */
	public function has($id): bool;

	/**
	 * Add a factory to the container
	 *
	 * @param string $id
	 * @param Callable  $value - a factory callable for the item
	 * @return ContainerInterface
	 */
	public function set(string $id, Callable $value): ContainerInterface;

	/**
	 * Set a specific instance in the container for an existing factory
	 *
	 * @param string $id
	 * @param mixed $value
	 * @return ContainerInterface
	 */
	public function setInstance(string $id, $value): ContainerInterface;

	/**
	 * Get a new instance of the specified item
	 *
	 * @param string $id
	 * @return mixed
	 */
	public function getNew($id);

	/**
	 * Determine whether a logger channel is registered
	 *
	 * @param  string $id The logger channel
	 * @return boolean
	 */
	public function hasLogger(string $id = 'default'): bool;

	/**
	 * Add a logger to the Container
	 *
	 * @param LoggerInterface $logger
	 * @param string          $id     The logger 'channel'
	 * @return ContainerInterface
	 */
	public function setLogger(LoggerInterface $logger, string $id = 'default'): ContainerInterface;

	/**
	 * Retrieve a logger for the selected channel
	 *
	 * @param  string $id The logger to retrieve
	 * @return LoggerInterface|null
	 */
	public function getLogger(string $id = 'default'): ?LoggerInterface;
}