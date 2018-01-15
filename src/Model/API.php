<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

/**
 * Base model for api interaction
 */
class API extends AbstractModel {

	/**
	 * Whether to use the MAL api
	 *
	 * @var boolean
	 */
	protected $useMALAPI;

	/**
	 * Sort the list entries by their title
	 *
	 * @param array $array
	 * @param string $sortKey
	 * @return void
	 */
	protected function sortByName(array &$array, string $sortKey)
	{
		$sort = [];

		foreach ($array as $key => $item)
		{
			$sort[$key] = $item[$sortKey]['titles'][0];
		}

		array_multisort($sort, SORT_ASC, $array);
	}
}