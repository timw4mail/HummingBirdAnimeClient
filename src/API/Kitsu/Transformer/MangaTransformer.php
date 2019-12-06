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
 * @copyright   2015 - 2019  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\JsonAPI;
use Aviat\AnimeClient\Types\MangaPage;
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transformer for anime description page
 */
final class MangaTransformer extends AbstractTransformer {

	/**
	 * Convert raw api response to a more
	 * logical and workable structure
	 *
	 * @param  array  $item API library item
	 * @return MangaPage
	 */
	public function transform($item): MangaPage
	{
		$genres = [];

		$item['included'] = JsonAPI::organizeIncluded($item['included']);

		if (array_key_exists('categories', $item['included']))
		{
			foreach ($item['included']['categories'] as $cat)
			{
				$genres[] = $cat['attributes']['title'];
			}
			sort($genres);
		}

		$title = $item['canonicalTitle'];
		$rawTitles = array_values($item['titles']);
		$titles = array_unique(array_diff($rawTitles, [$title]));

		$characters = [];
		$staff = [];

		if (array_key_exists('mediaCharacters', $item['included']))
		{
			$mediaCharacters = $item['included']['mediaCharacters'];

			foreach ($mediaCharacters as $rel)
			{
				// dd($rel);
				// $charId = $rel['relationships']['character']['data']['id'];
				$role = $rel['attributes']['role'];

				foreach ($rel['relationships']['character']['characters'] as $charId => $char)
				{
					if (array_key_exists($charId, $item['included']['characters']))
					{
						$characters[$role][$charId] = $char['attributes'];
					}
				}
			}
		}

		if (array_key_exists('mediaStaff', $item['included']))
		{
			foreach ($item['included']['mediaStaff'] as $id => $staffing)
			{
				$role = $staffing['attributes']['role'];

				foreach ($staffing['relationships']['person']['people'] as $personId => $personDetails)
				{
					if ( ! array_key_exists($role, $staff))
					{
						$staff[$role] = [];
					}

					$staff[$role][$personId] = [
						'id' => $personId,
						'name' => $personDetails['attributes']['name'] ?? '??',
						'image' => $personDetails['attributes']['image'],
					];
				}
			}
		}

		if ( ! empty($characters['main']))
		{
			uasort($characters['main'], function ($a, $b) {
				return $a['name'] <=> $b['name'];
			});
		}

		if ( ! empty($characters['supporting']))
		{
			uasort($characters['supporting'], function ($a, $b) {
				return $a['name'] <=> $b['name'];
			});
		}

		ksort($characters);
		ksort($staff);

		return new MangaPage([
			'characters' => $characters,
			'chapter_count' => $this->count($item['chapterCount']),
			'cover_image' => $item['posterImage']['small'],
			'genres' => $genres,
			'id' => $item['id'],
			'included' => $item['included'],
			'manga_type' => $item['mangaType'],
			'staff' => $staff,
			'synopsis' => $item['synopsis'],
			'title' => $title,
			'titles' => $titles,
			'url' => "https://kitsu.io/manga/{$item['slug']}",
			'volume_count' => $this->count($item['volumeCount']),
		]);
	}

	/**
	 * @return int|null|string
	 */
	private function count(int $value = NULL)
	{
		return ((int)$value === 0)
			? '-'
			: $value;
	}
}