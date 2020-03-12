<?php declare(strict_types=1);
/**
 * Ion
 *
 * Building blocks for web development
 *
 * PHP version 7.2
 *
 * @package     Ion
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.0.0
 * @link        https://git.timshomepage.net/aviat/ion
 */

namespace Aviat\Ion\Transformer;

use Aviat\Ion\StringWrapper;

use BadMethodCallException;

/**
 * Base class for data transformation
 */
abstract class AbstractTransformer implements TransformerInterface {

	use StringWrapper;

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
	 * @param  iterable $collection
	 * @return array
	 */
	public function transformCollection(iterable $collection): array
	{
		$list = (array)$collection;
		return array_map([$this, 'transform'], $list);
	}

	/**
	 * Untransform a set of structures
	 *
	 * Requires an 'untransform' method in the extending class
	 *
	 * @param iterable $collection
	 * @return array
	 */
	public function untransformCollection(iterable $collection): array
	{
		if ( ! method_exists($this, 'untransform'))
		{
			throw new BadMethodCallException('untransform() method does not exist.');
		}

		$list = (array)$collection;
		return array_map([$this, 'untransform'], $list);
	}
}
// End of AbstractTransformer.php