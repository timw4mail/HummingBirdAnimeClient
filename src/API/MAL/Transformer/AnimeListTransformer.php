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
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\MAL\Transformer;

use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transformer for updating MAL List
 */
class AnimeListTransformer extends AbstractTransformer {
	/**
	 * Transform MAL episode data to Kitsu episode data
	 *
	 * @param array $item
	 * @return array
	 */
	public function transform($item)
	{
		$rewatching = (array_key_exists('rewatching', $item) && $item['rewatching']);

		return [
			'id' => $item['mal_id'],
			'data' => [
				'status' => AnimeWatchingStatus::KITSU_TO_MAL[$item['watching_status']],
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
		$map = [
			'id' => $item['mal_id'],
			'data' => [
				'episode' => $item['data']['progress']
			]
		];

		$data =& $item['data'];

		foreach($item['data'] as $key => $value)
		{
			switch($key)
			{
				case 'notes':
					$map['data']['comments'] = $value;
				break;

				case 'rating':
					$map['data']['score'] = $value * 2;
				break;

				case 'reconsuming':
					$map['data']['enable_rewatching'] = (bool) $value;
				break;

				case 'reconsumeCount':
					$map['data']['times_rewatched'] = $value;
				break;

				case 'status':
					$map['data']['status'] = AnimeWatchingStatus::KITSU_TO_MAL[$value];
				break;

				default:
				break;
			}
		}

		return $map;
	}
}