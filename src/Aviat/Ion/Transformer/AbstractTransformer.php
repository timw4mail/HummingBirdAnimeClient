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

namespace Aviat\Ion\Transformer;

/**
 * Base class for data trasformation
 */
abstract class AbstractTransformer implements TransformerInterface {

	use \Aviat\Ion\StringWrapper;

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
		$list = (array)$collection;
		return array_map([$this, 'transform'], $list);
	}
}
// End of AbstractTransformer.php