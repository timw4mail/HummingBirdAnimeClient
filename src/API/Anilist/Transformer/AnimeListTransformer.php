<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Anilist\Transformer;

use Aviat\AnimeClient\API\Enum\AnimeWatchingStatus\Anilist as AnilistStatus;
use Aviat\AnimeClient\API\Enum\AnimeWatchingStatus\Kitsu as KitsuStatus;
use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\Types\{AnimeListItem, FormItem};

use Aviat\Ion\Transformer\AbstractTransformer;

use DateTime;

class AnimeListTransformer extends AbstractTransformer {

	public function transform($item): AnimeListItem
	{
		return new AnimeListItem([]);
	}

	/**
	 * Transform Anilist list item to Kitsu form update format
	 *
	 * @param array $item
	 * @return FormItem
	 */
	public function untransform(array $item): FormItem
	{
		$reconsuming = $item['status'] === AnilistStatus::REPEATING;

		return new FormItem([
			'id' => $item['id'],
			'mal_id' => $item['media']['idMal'],
			'data' => [
				'notes' => $item['notes'] ?? '',
				'private' => $item['private'],
				'progress' => $item['progress'],
				'rating' => $item['score'] ?? NULL,
				'reconsumeCount' => $item['repeat'],
				'reconsuming' => $reconsuming,
				'status' => $reconsuming
					? KitsuStatus::WATCHING
					:AnimeWatchingStatus::ANILIST_TO_KITSU[$item['status']],
				'updatedAt' => (new DateTime())
					->setTimestamp($item['updatedAt'])
					->format(DateTime::W3C)
			],
		]);
	}
}