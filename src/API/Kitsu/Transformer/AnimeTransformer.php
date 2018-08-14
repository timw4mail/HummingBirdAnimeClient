<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
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

use Aviat\AnimeClient\API\{JsonAPI, Kitsu};
use Aviat\AnimeClient\Types\Anime;
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transformer for anime description page
 */
final class AnimeTransformer extends AbstractTransformer {

	/**
	 * Convert raw api response to a more
	 * logical and workable structure
	 *
	 * @param  array  $item API library item
	 * @return Anime
	 */
	public function transform($item): Anime
	{
		$item['included'] = JsonAPI::organizeIncludes($item['included']);
		$genres = $item['included']['categories'] ?? [];
		$item['genres'] = array_column($genres, 'title') ?? [];
		sort($item['genres']);

		$title = $item['canonicalTitle'];
		$titles = array_diff($item['titles'], [$title]);

		return new Anime([
			'age_rating' => $item['ageRating'],
			'age_rating_guide' => $item['ageRatingGuide'],
			'cover_image' => $item['posterImage']['small'],
			'episode_count' => $item['episodeCount'],
			'episode_length' => $item['episodeLength'],
			'genres' => $item['genres'],
			'id' => $item['id'],
			'show_type' => $this->string($item['showType'])->upperCaseFirst()->__toString(),
			'slug' => $item['slug'],
			'status' => Kitsu::getAiringStatus($item['startDate'], $item['endDate']),
			'streaming_links' => Kitsu::parseStreamingLinks($item['included']),
			'synopsis' => $item['synopsis'],
			'title' => $title,
			'titles' => $titles,
			'trailer_id' => $item['youtubeVideoId'],
			'url' => "https://kitsu.io/anime/{$item['slug']}",
		]);
	}
}