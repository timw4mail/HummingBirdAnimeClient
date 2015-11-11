<?php

namespace Aviat\Ion\Di;

/**
 * Interface for a class that is aware of the Di Container
 */
interface ContainerAwareInterface {

	/**
	 * Set the container for the current object
	 *
	 * @param ContainerInterface $container
	 * @return void
	 */
	public function setContainer(ContainerInterface $container);

	/**
	 * Get the container object
	 *
	 * @return ContainerInterface
	 */
	public function getContainer();

}
// End of ContainerAwareInterface.php