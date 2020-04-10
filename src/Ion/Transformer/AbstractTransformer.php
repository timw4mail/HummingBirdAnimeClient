<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
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