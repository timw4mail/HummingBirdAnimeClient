<?php

namespace Aviat\Ion;

use Aviat\Ion\Type\StringType;

/**
 * Trait to add convenience method for creating StringType objects
 */
trait StringWrapper {

	/**
	 * Wrap the String in the Stringy class
	 *
	 * @param string $str
	 * @return StringType
	 */
	public function string($str)
	{
		return StringType::create($str);
	}
}
// End of StringWrapper.php