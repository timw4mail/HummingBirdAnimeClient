<?php

namespace Aviat\Ion;

use Aviat\Ion\Type\ArrayType;

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