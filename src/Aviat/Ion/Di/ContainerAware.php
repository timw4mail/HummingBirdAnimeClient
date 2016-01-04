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
	 * @return $this
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->container = $container;
		return $this;
	}

	/**
	 * Get the container object
	 *
	 * @return ContainerInterface
	 */
	public function getContainer()
	{
		return $this->container;
	}
}
// End of ContainerAware.php