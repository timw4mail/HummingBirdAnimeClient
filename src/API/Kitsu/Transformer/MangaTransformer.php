<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package	 AnimeListClient
 * @author	  Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license	 http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version	 4.0
 * @link		https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transformer for anime description page
 */
class MangaTransformer extends AbstractTransformer {

	/**
	 * Convert raw api response to a more
	 * logical and workable structure
	 *
	 * @param  array  $item API library item
	 * @return array
	 */
	public function transform($item)
	{
		return [
			'title' => $item['canonicalTitle'],
			'en_title' => $item['titles']['en'],
			'jp_title' => $item['titles']['en_jp'],
			'cover_image' => $item['posterImage']['small'],
			'manga_type' => $item['mangaType'],
			'chapter_count' => $this->count($item['chapterCount']),
			'volume_count' => $this->count($item['volumeCount']),
			'synopsis' => $item['synopsis'],
			'url' => "https://kitsu.io/manga/{$item['slug']}",
			'genres' => $item['genres'],
		];
	}

	private function count(int $value = null)
	{
		return ((int)$value === 0)
			? '-'
			: $value;
	}
}