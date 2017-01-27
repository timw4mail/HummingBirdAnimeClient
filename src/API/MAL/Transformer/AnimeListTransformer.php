<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\MAL;

use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transformer for updating MAL List
 */
class AnimeListTransformer extends AbstractTransformer {

	public function transform($item)
	{
		$rewatching = 'false';
		if (array_key_exists('rewatching', $item) && $item['rewatching'])
		{
			$rewatching = 'true';
		}

		return [
			'id' => $item['id'],
			'data' => [
				'status' => $item['watching_status'],
				'rating' => $item['user_rating'],
				'rewatch_value' => (int) $rewatching,
				'times_rewatched' => $item['rewatched'],
				'comments' => $item['notes'],
				'episode' => $item['episodes_watched']
			]
		];
	}
}