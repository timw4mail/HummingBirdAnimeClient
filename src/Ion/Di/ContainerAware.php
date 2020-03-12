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

/**
 * Trait implementation of ContainerAwareInterface
 */
trait ContainerAware {

	/**
	 * Di Container
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Set the container for the current object
	 *
	 * @param ContainerInterface $container
	 * @return self
	 */
	public function setContainer(ContainerInterface $container): self
	{
		$this->container = $container;
		return $this;
	}

	/**
	 * Get the container object
	 *
	 * @return ContainerInterface
	 */
	public function getContainer(): ContainerInterface
	{
		return $this->container;
	}
}
// End of ContainerAware.php