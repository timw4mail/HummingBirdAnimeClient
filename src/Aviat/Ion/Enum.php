<?php

namespace Aviat\Ion;

use ReflectionClass;

abstract class Enum {

	use StaticInstance;

	/**
	 * Return the list of constant values for the Enum
	 *
	 * @return array
	 */
	protected function getConstList()
	{
		$reflect = new ReflectionClass($this);
		return $reflect->getConstants();
	}

	/**
	 * Verify that a constant value is valid
	 * @param  mixed  $key
	 * @return boolean
	 */
	protected function isValid($key)
	{
		$values = array_values($this->getConstList());
		return in_array($key, $values);
	}
}
// End of Enum.php