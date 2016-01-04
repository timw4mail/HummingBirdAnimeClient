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

use Aviat\Ion\Type\ArrayType;

/**
 * Wrapper to shortcut creating ArrayType objects
 */
trait ArrayWrapper {

	/**
	 * Convenience method for wrapping an array
	 * with the array type class
	 *
	 * @param array $arr
	 * @return ArrayType
	 */
	public function arr(array $arr)
	{
		return new ArrayType($arr);
	}
}
// End of ArrayWrapper.php