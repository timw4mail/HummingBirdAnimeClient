<?php

namespace Aviat\Ion\Di;

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
