<?php

namespace Aviat\Ion\Di;

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