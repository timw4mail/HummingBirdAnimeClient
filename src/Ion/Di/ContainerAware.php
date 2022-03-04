<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Di;

/**
 * Trait implementation of ContainerAwareInterface
 */
trait ContainerAware
{
	/**
	 * Di Container
	 */
	protected ContainerInterface $container;

	/**
	 * Set the container for the current object
	 */
	public function setContainer(ContainerInterface $container): self
	{
		$this->container = $container;

		return $this;
	}

	/**
	 * Get the container object
	 */
	public function getContainer(): ContainerInterface
	{
		return $this->container;
	}
}

// End of ContainerAware.php
