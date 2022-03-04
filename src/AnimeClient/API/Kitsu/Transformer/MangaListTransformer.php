<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\Kitsu;
use Aviat\AnimeClient\Types\{
	FormItem, FormItemData,
	MangaListItem, MangaListItemDetail
};
use Aviat\Ion\Transformer\AbstractTransformer;
use Aviat\Ion\Type\StringType;

/**
 * Data transformation class for zippered Hummingbird manga
 */
final class MangaListTransformer extends AbstractTransformer
{
	/**
	 * Remap zipped anime data to a more logical form
	 *
	 * @param array|object $item manga entry item
	 */
	public function transform(array|object $item): MangaListItem
	{
		$item = (array) $item;
		$mangaId = $item['media']['id'];
		$manga = $item['media'];

		$genres = [];

		// Rating is 1-20, we want 1-10
		$rating = (int) $item['rating'] !== 0
			? $item['rating'] / 2
			: '-';

		$totalChapters = ((int) $manga['chapterCount'] !== 0)
			? $manga['chapterCount']
			: '-';

		$totalVolumes = ((int) $manga['volumeCount'] !== 0)
			? $manga['volumeCount']
			: '-';

		$readChapters = ((int) $item['progress'] !== 0)
			? $item['progress']
			: '-';

		$MALid = NULL;

		$mappings = $manga['mappings']['nodes'] ?? [];
		if ( ! empty($mappings))
		{
			foreach ($mappings as $mapping)
			{
				if ($mapping['externalSite'] === 'MYANIMELIST_MANGA')
				{
					$MALid = $mapping['externalId'];
					break;
				}
			}
		}

		$titles = Kitsu::getFilteredTitles($manga['titles']);
		$title = $manga['titles']['canonical'];

		return MangaListItem::from([
			'id' => $item['id'],
			'mal_id' => $MALid,
			'chapters' => [
				'read' => $readChapters,
				'total' => $totalChapters,
			],
			'volumes' => [
				'read' => '-', //$item['attributes']['volumes_read'],
				'total' => $totalVolumes,
			],
			'manga' => MangaListItemDetail::from([
				'genres' => $genres,
				'id' => $mangaId,
				'image' => Kitsu::getPosterImage($manga),
				'slug' => $manga['slug'],
				'title' => $title,
				'titles' => $titles,
				'type' => (string) StringType::from($manga['subtype'])->toLowerCase()->upperCaseFirst(),
				'url' => 'https://kitsu.io/manga/' . $manga['slug'],
			]),
			'reading_status' => strtolower($item['status']),
			'notes' => $item['notes'],
			'rereading' => (bool) $item['reconsuming'],
			'reread' => $item['reconsumeCount'],
			'user_rating' => $rating,
		]);
	}

	/**
	 * Untransform data to update the api
	 *
	 * @param array $item
	 */
	public function untransform($item): FormItem
	{
		$rereading = array_key_exists('rereading', $item) && (bool) $item['rereading'];

		$map = FormItem::from([
			'id' => $item['id'],
			'mal_id' => $item['mal_id'],
			'data' => FormItemData::from([
				'status' => $item['status'],
				'reconsuming' => $rereading,
				'reconsumeCount' => (int) $item['reread_count'],
				'notes' => $item['notes'],
			]),
		]);

		if (is_numeric($item['chapters_read']) && $item['chapters_read'] > 0)
		{
			$map['data']['progress'] = (int) $item['chapters_read'];
		}

		if (is_numeric($item['new_rating']) && $item['new_rating'] > 0)
		{
			$map['data']['ratingTwenty'] = $item['new_rating'] * 2;
		}

		return $map;
	}
}

// End of MangaListTransformer.php
