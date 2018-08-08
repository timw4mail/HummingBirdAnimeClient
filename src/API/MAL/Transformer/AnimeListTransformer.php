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

namespace Aviat\AnimeClient\API\MAL\Transformer;

use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\Types\{AnimeFormItem, AnimeFormItemData};
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transformer for updating MAL List
 */
final class AnimeListTransformer extends AbstractTransformer {
	/**
	 * Identity transformation
	 *
	 * @param array $item
	 * @return array
	 */
	public function transform($item)
	{
		return $item;
	}

	/**
	 * Transform Kitsu episode data to MAL episode data
	 *
	 * @param array $item
	 * @return AnimeFormItem
	 */
	public function untransform(array $item): AnimeFormItem
	{
		$map = new AnimeFormItem([
			'id' => $item['mal_id'],
			'data' => new AnimeFormItemData([]),
		]);

		foreach($item['data'] as $key => $value)
		{
			switch($key)
			{
				case 'progress':
					$map['data']['episode'] = $value;
				break;

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