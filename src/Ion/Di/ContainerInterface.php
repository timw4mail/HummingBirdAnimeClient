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
interface ContainerInterface
{
	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string $id Identifier of the entry to look for.
	 * @throws Exception\NotFoundException No entry was found for this identifier.
	 * @throws Exception\ContainerException Error while retrieving the entry.
	 * @return mixed Entry.
	 */
	public function get(string $id): mixed;

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * @param string $id Identifier of the entry to look for.
	 */
	public function has(string $id): bool;

	/**
	 * Add a factory to the container
	 *
	 * @param callable $value - a factory callable for the item
	 */
	public function set(string $id, callable $value): ContainerInterface;

	/**
	 * Set a specific instance in the container for an existing factory
	 */
	public function setInstance(string $id, mixed $value): ContainerInterface;

	/**
	 * Get a new instance of the specified item
	 */
	public function getNew(string $id): mixed;

	/**
	 * Determine whether a logger channel is registered
	 *
	 * @param string $id The logger channel
	 */
	public function hasLogger(string $id = 'default'): bool;

	/**
	 * Add a logger to the Container
	 *
	 * @param string $id The logger 'channel'
	 */
	public function setLogger(LoggerInterface $logger, string $id = 'default'): ContainerInterface;

	/**
	 * Retrieve a logger for the selected channel
	 *
	 * @param string $id The logger to retrieve
	 */
	public function getLogger(string $id = 'default'): ?LoggerInterface;
}
