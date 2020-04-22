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

namespace Aviat\AnimeClient\API\Anilist\Transformer;

use Aviat\AnimeClient\API\Enum\MangaReadingStatus\Anilist as AnilistStatus;
use Aviat\AnimeClient\API\Mapping\MangaReadingStatus;
use Aviat\AnimeClient\Types\MangaListItem;
use Aviat\AnimeClient\Types\FormItem;

use Aviat\Ion\Transformer\AbstractTransformer;

use DateTime;

class MangaListTransformer extends AbstractTransformer {

	public function transform($item)
	{
		return new MangaListItem([]);
	}

	/**
	 * Transform Anilist list item to Kitsu form update format
	 *
	 * @param array $item
	 * @return FormItem
	 */
	public function untransform(array $item): FormItem
	{
		return new FormItem([
			'id' => $item['id'],
			'mal_id' => $item['media']['idMal'],
			'data' => [
				'notes' => $item['notes'] ?? '',
				'private' => $item['private'],
				'progress' => $item['progress'],
				'rating' => $item['score'],
				'reconsumeCount' => $item['repeat'],
				'reconsuming' => $item['status'] === AnilistStatus::REPEATING,
				'status' => MangaReadingStatus::ANILIST_TO_KITSU[$item['status']],
				'updatedAt' => (new DateTime())
					->setTimestamp($item['updatedAt'])
					->format(DateTime::W3C),
			]
		]);
	}
}