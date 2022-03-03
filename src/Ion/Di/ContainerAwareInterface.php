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

/**
 * Interface for a class that is aware of the Di Container
 */
interface ContainerAwareInterface
{
	/**
	 * Set the container for the current object
	 *
	 * @return void
	 */
	public function setContainer(ContainerInterface $container);

	/**
	 * Get the container object
	 */
	public function getContainer(): ContainerInterface;
}

// End of ContainerAwareInterface.php
