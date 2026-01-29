<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Anilist\Transformer;

use Aviat\AnimeClient\API\{Enum, Mapping};
use Aviat\AnimeClient\Types\{AnimeListItem, FormItem};
use Aviat\Ion\Transformer\AbstractTransformer;
use DateTime;
use DateTimeInterface;

/**
 * @extends AbstractTransformer<AnimeListItem>
 */
class AnimeListTransformer extends AbstractTransformer
{
	/**
	 * @param array<mixed>|object $item
	 * @return AnimeListItem
	 */
	#[\Override]
	public function transform(array|object $item): AnimeListItem
	{
		return AnimeListItem::from([]);
	}

	/**
	 * Transform Anilist list item to Kitsu form update format
	 * @param array<string, mixed> $item
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
				'rating' => $item['score'] ?? null,
				'reconsumeCount' => $item['repeat'],
				'reconsuming' => $reconsuming,
				'status' => $reconsuming
					? Enum\AnimeWatchingStatus\Kitsu::WATCHING
					: Mapping\AnimeWatchingStatus::ANILIST_TO_KITSU[$item['status']],
				'updatedAt' => new DateTime()
					->setTimestamp($item['updatedAt'])
					->format(DateTimeInterface::W3C),
			],
		]);
	}
}
