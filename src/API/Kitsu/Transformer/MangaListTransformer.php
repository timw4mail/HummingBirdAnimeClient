<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
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

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu;
use Aviat\AnimeClient\Types\{
	MangaFormItem, MangaFormItemData,
	MangaListItem, MangaListItemDetail
};
use Aviat\Ion\StringWrapper;
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Data transformation class for zippered Hummingbird manga
 */
final class MangaListTransformer extends AbstractTransformer {

	use StringWrapper;

	/**
	 * Remap zipped anime data to a more logical form
	 *
	 * @param  array  $item manga entry item
	 * @return MangaListItem
	 */
	public function transform($item): MangaListItem
	{
		$included = $item['included'];
		$mangaId = $item['relationships']['media']['data']['id'];
		$manga = $included['manga'][$mangaId];

		$genres = array_column($manga['relationships']['genres'], 'name') ?? [];
		sort($genres);

		$rating = (int) $item['attributes']['ratingTwenty'] !== 0
			? $item['attributes']['ratingTwenty'] / 2
			: '-';

		$totalChapters = ((int) $manga['chapterCount'] !== 0)
			? $manga['chapterCount']
			: '-';

		$totalVolumes = ((int) $manga['volumeCount'] !== 0)
			? $manga['volumeCount']
			: '-';

		$readChapters = ((int) $item['attributes']['progress'] !== 0)
			? $item['attributes']['progress']
			: '-';

		$MALid = NULL;

		if (array_key_exists('mappings', $manga['relationships']))
		{
			foreach ($manga['relationships']['mappings'] as $mapping)
			{
				if ($mapping['externalSite'] === 'myanimelist/manga')
				{
					$MALid = $mapping['externalId'];
					break;
				}
			}
		}

		$titles = Kitsu::filterTitles($manga);
		$title = array_shift($titles);

		$map = new MangaListItem([
			'id' => $item['id'],
			'mal_id' => $MALid,
			'chapters' => [
				'read' => $readChapters,
				'total' => $totalChapters
			],
			'volumes' => [
				'read' => '-', //$item['attributes']['volumes_read'],
				'total' => $totalVolumes
			],
			'manga' => new MangaListItemDetail([
				'genres' => $genres,
				'id' => $mangaId,
				'image' => $manga['posterImage']['small'],
				'slug' => $manga['slug'],
				'title' => $title,
				'titles' => $titles,
				'type' => $manga['mangaType'],
				'url' => 'https://kitsu.io/manga/' . $manga['slug'],
			]),
			'reading_status' => $item['attributes']['status'],
			'notes' => $item['attributes']['notes'],
			'rereading' => (bool)$item['attributes']['reconsuming'],
			'reread' => $item['attributes']['reconsumeCount'],
			'user_rating' => $rating,
		]);

		return $map;
	}

	/**
	 * Untransform data to update the api
	 *
	 * @param  array $item
	 * @return MangaFormItem
	 */
	public function untransform($item): MangaFormItem
	{
		$rereading = array_key_exists('rereading', $item) && (bool)$item['rereading'];

		$map = new MangaFormItem([
			'id' => $item['id'],
			'mal_id' => $item['mal_id'],
			'data' => new MangaFormItemData([
				'status' => $item['status'],
				'reconsuming' => $rereading,
				'reconsumeCount' => (int)$item['reread_count'],
				'notes' => $item['notes'],
			]),
		]);

		if (is_numeric($item['chapters_read']) && $item['chapters_read'] > 0)
		{
			$map['data']['progress'] = (int)$item['chapters_read'];
		}

		if (is_numeric($item['new_rating']) && $item['new_rating'] > 0)
		{
			$map['data']['ratingTwenty'] = $item['new_rating'] * 2;
		}

		return $map;
	}
}
// End of MangaListTransformer.php