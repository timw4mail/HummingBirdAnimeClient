<?php

namespace Aviat\Ion\Transformer;

interface TransformerInterface {

	/**
	 * Mutate the data structure
	 *
	 * @param array|object $item
	 * @return mixed
	 */
	public function transform($item);
}