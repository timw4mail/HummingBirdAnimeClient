<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\{JsonAPI, Kitsu};
use Aviat\AnimeClient\Types\AnimePage;
use Aviat\Ion\Transformer\AbstractTransformer;
use Aviat\Ion\Type\StringType;

/**
 * Transformer for anime description page
 */
final class AnimeTransformer extends AbstractTransformer {

	/**
	 * Convert raw api response to a more
	 * logical and workable structure
	 *
	 * @param  array  $item API library item
	 * @return AnimePage
	 */
	public function transform($item): AnimePage
	{
		$item['included'] = JsonAPI::organizeIncludes($item['included']);
		$genres = $item['included']['categories'] ?? [];
		$item['genres'] = array_column($genres, 'title') ?? [];
		sort($item['genres']);

		$title = $item['canonicalTitle'];
		$titles = Kitsu::filterTitles($item);
		$titles_more = Kitsu::getTitles($item);

		$characters = [];
		$staff = [];

		if (array_key_exists('animeCharacters', $item['included']))
		{
			$animeCharacters = $item['included']['animeCharacters'];

			foreach ($animeCharacters as $rel)
			{
				$charId = $rel['relationships']['character']['data']['id'];
				$role = $rel['role'];

				if (array_key_exists($charId, $item['included']['characters']))
				{
					$characters[$role][$charId] = $item['included']['characters'][$charId];
				}
			}
		}

		if (array_key_exists('mediaStaff', $item['included']))
		{
			foreach ($item['included']['mediaStaff'] as $id => $staffing)
			{
				$personId = $staffing['relationships']['person']['data']['id'];
				$personDetails = $item['included']['people'][$personId];

				$role = $staffing['role'];

				if ( ! array_key_exists($role, $staff))
				{
					$staff[$role] = [];
				}

				$staff[$role][$personId] = [
					'id' => $personId,
					'name' => $personDetails['name'] ?? '??',
					'image' => $personDetails['image'],
				];

				usort($staff[$role], function ($a, $b) {
					return $a['name'] <=> $b['name'];
				});
			}
		}

		if ( ! empty($characters['main']))
		{
			uasort($characters['main'], static function ($a, $b) {
				return $a['name'] <=> $b['name'];
			});
		}

		if ( ! empty($characters['supporting']))
		{
			uasort($characters['supporting'], static function ($a, $b) {
				return $a['name'] <=> $b['name'];
			});
		}

		ksort($characters);
		ksort($staff);

		return AnimePage::from([
			'age_rating' => $item['ageRating'],
			'age_rating_guide' => $item['ageRatingGuide'],
			'characters' => $characters,
			'cover_image' => $item['posterImage']['small'],
			'episode_count' => $item['episodeCount'],
			'episode_length' => $item['episodeLength'],
			'genres' => $item['genres'],
			'id' => $item['id'],
			'included' => $item['included'],
			'show_type' => (string)StringType::from($item['showType'])->upperCaseFirst(),
			'slug' => $item['slug'],
			'staff' => $staff,
			'status' => Kitsu::getAiringStatus($item['startDate'], $item['endDate']),
			'streaming_links' => Kitsu::parseStreamingLinks($item['included']),
			'synopsis' => $item['synopsis'],
			'title' => $title,
			'titles' => $titles,
			'titles_more' => $titles_more,
			'trailer_id' => $item['youtubeVideoId'],
			'url' => "https://kitsu.io/anime/{$item['slug']}",
		]);
	}
}