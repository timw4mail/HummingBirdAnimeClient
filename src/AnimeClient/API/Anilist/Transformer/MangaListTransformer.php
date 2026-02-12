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

use Aviat\AnimeClient\API\Enum;
use Aviat\AnimeClient\API\Mapping;
use Aviat\AnimeClient\Types\FormItem;
use Aviat\AnimeClient\Types\MangaListItem;
use Aviat\Ion\Transformer\AbstractTransformer;
use DateTime;
use DateTimeInterface;

/**
 * @extends AbstractTransformer<MangaListItem>
 */
class MangaListTransformer extends AbstractTransformer
{
	/**
	 * @param array<mixed>|object $item
	 * @return MangaListItem
	 */
	#[\Override]
	public function transform(array|object $item): MangaListItem
	{
		return MangaListItem::from([]);
	}

	/**
	 * Transform Anilist list item to Kitsu form update format
	 * @param array<string, mixed> $item
	 */
	public function untransform(array $item): FormItem
	{
		$reconsuming = $item['status'] === Enum\MangaReadingStatus\Anilist::REPEATING;

		return FormItem::from([
			'id' => $item['id'],
			'mal_id' => $item['media']['idMal'],
			'data' => [
				'notes' => $item['notes'] ?? '',
				'private' => $item['private'],
				'progress' => $item['progress'],
				'rating' => $item['score'],
				'reconsumeCount' => $item['repeat'],
				'reconsuming' => $reconsuming,
				'status' => $reconsuming
					? Enum\MangaReadingStatus\Kitsu::READING
					: Mapping\MangaReadingStatus::ANILIST_TO_KITSU[$item['status']],
				'updatedAt' => new DateTime()
					->setTimestamp($item['updatedAt'])
					->format(DateTimeInterface::W3C),
			],
		]);
	}
}
