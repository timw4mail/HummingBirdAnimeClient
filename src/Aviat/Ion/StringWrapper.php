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