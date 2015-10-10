<?php

namespace Aviat\Ion\Di;

/**
 * Interface for the Dependency Injection Container
 */
interface ContainerInterface extends \Interop\Container\ContainerInterface {

	/**
	 * Add a value to the container
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return ContainerInterface
	 */
	public function set($key, $value);
}