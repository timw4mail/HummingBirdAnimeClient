<?php

namespace Aviat\Ion\Transformer;

abstract class AbstractTransformer implements TransformerInterface {

	/**
	 * Mutate the data structure
	 *
	 * @param array|object $item
	 * @return mixed
	 */
	abstract public function transform($item);

	/**
	 * Transform a set of structures
	 *
	 * @param  array|object $collection
	 * @return array
	 */
	public function transform_collection($collection)
	{
		$list = (array) $collection;
		return array_map([$this, 'transform'], $list);
	}
}
// End of AbstractTransformer.php