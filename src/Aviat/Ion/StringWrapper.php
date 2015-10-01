<?php

namespace Aviat\Ion;

use Stringy\Stringy as S;

trait StringWrapper {

	/**
	 * Wrap the String in the Stringy class
	 *
	 * @param string $str
	 * @return Stringy\Stringy
	 */
	public function string($str)
	{
		return S::create($str);
	}
}
// End of StringWrapper.php