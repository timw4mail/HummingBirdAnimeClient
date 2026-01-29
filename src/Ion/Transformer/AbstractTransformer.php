<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2025  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Transformer;

use BadMethodCallException;

/**
 * Base class for data transformation
 * @template T
 * @implements TransformerInterface<T>
 */
abstract class AbstractTransformer implements TransformerInterface
{
	/**
	 * Mutate the data structure
	 *
	 * @param array<mixed>|object $item
	 * @return T
	 */
	#[\Override]
	abstract public function transform(array|object $item);

	/**
	 * Transform a set of structures
	 *
	 * @param iterable<array<mixed>|T> $collection
	 * @return array<string, mixed>
	 */
	public function transformCollection(iterable $collection): array
	{
		$list = (array) $collection;

		return array_map($this->transform(...), $list);
	}

	/**
	 * Untransform a set of structures
	 *
	 * Requires an 'untransform' method in the extending class
	 * @param iterable<array<mixed>|T> $collection
	 * @return array<string, mixed>
	 */
	public function untransformCollection(iterable $collection): array
	{
		if (! method_exists($this, 'untransform'))
		{
			throw new BadMethodCallException('untransform() method does not exist.');
		}

		$list = (array) $collection;

		return array_map([$this, 'untransform'], $list);
	}
}

// End of AbstractTransformer.php
