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

		foreach($item['included'] as $included)
		{
			if ($included['type'] === 'categories')
			{
				$genres[] = $included['attributes']['title'];
			}
		}

		sort($genres);

		$title = $item['canonicalTitle'];
		$rawTitles = array_values($item['titles']);
		$titles = array_unique(array_diff($rawTitles, [$title]));

		return new MangaPage([
			'id' => $item['id'],
			'title' => $title,
			'titles' => $titles,
			'cover_image' => $item['posterImage']['small'],
			'manga_type' => $item['mangaType'],
			'chapter_count' => $this->count($item['chapterCount']),
			'volume_count' => $this->count($item['volumeCount']),
			'synopsis' => $item['synopsis'],
			'url' => "https://kitsu.io/manga/{$item['slug']}",
			'genres' => $genres,
		]);
	}

	private function count(int $value = NULL)
	{
		return ((int)$value === 0)
			? '-'
			: $value;
	}
}