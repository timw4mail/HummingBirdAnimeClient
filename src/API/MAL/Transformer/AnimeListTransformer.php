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

namespace Aviat\AnimeClient\API\MAL\Transformer;

use Aviat\AnimeClient\API\Kitsu\Enum\AnimeWatchingStatus;
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transformer for updating MAL List
 */
class AnimeListTransformer extends AbstractTransformer {
	
	const statusMap = [
		AnimeWatchingStatus::WATCHING => '1',
		AnimeWatchingStatus::COMPLETED => '2',
		AnimeWatchingStatus::ON_HOLD => '3',
		AnimeWatchingStatus::DROPPED => '4',
		AnimeWatchingStatus::PLAN_TO_WATCH => '6'
	];

	public function transform($item)
	{
		$rewatching = (array_key_exists('rewatching', $item) && $item['rewatching']);

		return [
			'id' => $item['mal_id'],
			'data' => [
				'status' => self::statusMap[$item['watching_status']],
				'rating' => $item['user_rating'],
				'rewatch_value' => (int) $rewatching,
				'times_rewatched' => $item['rewatched'],
				'comments' => $item['notes'],
				'episode' => $item['episodes_watched']
			]
		];
	}
	
	/**
	 * Transform Kitsu episode data to MAL episode data
	 *
	 * @param array $item
	 * @return array 
	 */
	public function untransform(array $item): array
	{
		$rewatching = (array_key_exists('reconsuming', $item['data']) && $item['data']['reconsuming']);
		
		$map = [
			'id' => $item['mal_id'],
			'data' => [
				'episode' => $item['data']['progress'],
				// 'enable_rewatching' => $rewatching,
				// 'times_rewatched' => $item['data']['reconsumeCount'],
				// 'comments' => $item['data']['notes'],
			]
		];
		
		if (array_key_exists('rating', $item['data']))
		{
			$map['data']['score'] = $item['data']['rating'] * 2;
		}
		
		if (array_key_exists('status', $item['data']))
		{
			$map['data']['status'] = self::statusMap[$item['data']['status']];
		}
		
		return $map;
	}
}