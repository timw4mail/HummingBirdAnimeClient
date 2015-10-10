<?php

namespace Aviat\Ion\Transformer;

/**
 * Interface for data transformation classes
 */
interface TransformerInterface {

	/**
	 * Mutate the data structure
	 *
	 * @param array|object $item
	 * @return mixed
	 */
	public function transform($item);
}