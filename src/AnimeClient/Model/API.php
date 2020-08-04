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
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

/**
 * Base model for api interaction
 */
abstract class API {
	/**
	 * Sort the list entries by their title
	 *
	 * @param array $array
	 * @param string $sortKey
	 * @return void
	 */
	protected function sortByName(array &$array, string $sortKey): void
	{
		if (empty($array))
		{
			return;
		}

		$sort = [];

		foreach ($array as $key => $item)
		{
			$sort[$key] = $item[$sortKey]['title'];
		}

		array_multisort($sort, SORT_ASC, $array);

		// Re-key array items by their ids
		if (array_key_exists('id', (array)$array[0]))
		{
			$keyed = [];

			foreach($array as $item)
			{
				$keyed[$item['id']] = $item;
			}

			$array = $keyed;
		}
	}
}