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

namespace Aviat\Ion;

use ReflectionClass;

/**
 * Class emulating an enumeration type
 *
 * @method bool isValid(mixed $key)
 * @method array getConstList()
 */
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