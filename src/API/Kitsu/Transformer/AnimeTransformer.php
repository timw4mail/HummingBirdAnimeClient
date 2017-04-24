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
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\{JsonAPI, Kitsu};
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transformer for anime description page
 */
class AnimeTransformer extends AbstractTransformer {

	/**
	 * Convert raw api response to a more
	 * logical and workable structure
	 *
	 * @param  array  $item API library item
	 * @return array
	 */
	public function transform($item)
	{
		$item['included'] = JsonAPI::organizeIncludes($item['included']);
		$item['genres'] = array_column($item['included']['genres'], 'name') ?? [];
		sort($item['genres']);

		$titles = Kitsu::filterTitles($item);

		return [
			'id' => $item['id'],
			'slug' => $item['slug'],
			'title' => $titles[0],
			'titles' => $titles,
			'status' => Kitsu::getAiringStatus($item['startDate'], $item['endDate']),
			'cover_image' => $item['posterImage']['small'],
			'show_type' => $this->string($item['showType'])->upperCaseFirst()->__toString(),
			'episode_count' => $item['episodeCount'],
			'episode_length' => $item['episodeLength'],
			'synopsis' => $item['synopsis'],
			'age_rating' => $item['ageRating'],
			'age_rating_guide' => $item['ageRatingGuide'],
			'url' => "https://kitsu.io/anime/{$item['slug']}",
			'genres' => $item['genres'],
			'streaming_links' => Kitsu::parseStreamingLinks($item['included'])
		];
	}
}