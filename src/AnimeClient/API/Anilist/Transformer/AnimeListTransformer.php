<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Anilist\Transformer;

use Aviat\AnimeClient\API\{Enum, Mapping};
use Aviat\AnimeClient\Types\{AnimeListItem, FormItem};

use Aviat\Ion\Transformer\AbstractTransformer;

use DateTime;
use DateTimeInterface;

class AnimeListTransformer extends AbstractTransformer
{
	public function transform(array|object $item): AnimeListItem
	{
		return AnimeListItem::from([]);
	}

	/**
	 * Transform Anilist list item to Kitsu form update format
	 */
	public function untransform(array $item): FormItem
	{
		$reconsuming = $item['status'] === Enum\AnimeWatchingStatus\Anilist::REPEATING;

		return FormItem::from([
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
					? Enum\AnimeWatchingStatus\Kitsu::WATCHING
					: Mapping\AnimeWatchingStatus::ANILIST_TO_KITSU[$item['status']],
				'updatedAt' => (new DateTime())
					->setTimestamp($item['updatedAt'])
					->format(DateTimeInterface::W3C),
			],
		]);
	}
}
