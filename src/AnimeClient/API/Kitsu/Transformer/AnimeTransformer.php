<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\Kitsu;
use Aviat\AnimeClient\Types\AnimePage;
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
	 * @return AnimePage
	 */
	public function transform($item): AnimePage
	{
		$base = $item['data']['findAnimeBySlug'] ?? $item['data']['findAnimeById'] ?? $item['data']['randomMedia'];
		$characters = [];
		$links = [];
		$staff = [];
		$genres = array_map(fn ($genre) => $genre['title']['en'], $base['categories']['nodes']);

		sort($genres);

		$title = $base['titles']['canonical'];
		$titles = Kitsu::getTitles($base['titles']);
		$titles_more = Kitsu::filterLocalizedTitles($base['titles']);

		if (count($base['characters']['nodes']) > 0)
		{
			foreach ($base['characters']['nodes'] as $rawCharacter)
			{
				$type = mb_strtolower($rawCharacter['role']);
				if ( ! isset($characters[$type]))
				{
					$characters[$type] = [];
				}

				$details = $rawCharacter['character'];
				$characters[$type][$details['id']] = [
					'image' => $details['image'],
					'name' => $details['names']['canonical'],
					'slug' => $details['slug'],
				];
			}

			foreach (array_keys($characters) as $type)
			{
				if (empty($characters[$type]))
				{
					unset($characters[$type]);
				}
				else
				{
					uasort($characters[$type], fn($a, $b) => $a['name'] <=> $b['name']);
				}
			}

			krsort($characters);
		}

		if (count($base['staff']['nodes']) > 0)
		{
			foreach ($base['staff']['nodes'] as $staffing)
			{
				$person = $staffing['person'];
				$role = $staffing['role'];
				$name = $person['names']['localized'][$person['names']['canonical']];

				if ( ! array_key_exists($role, $staff))
				{
					$staff[$role] = [];
				}

				$staff[$role][$person['id']] = [
					'id' => $person['id'],
					'name' => $name,
					'image' => [
						'original' => $person['image']['original']['url'],
					],
					'slug' => $person['slug'],
				];

				usort($staff[$role], fn ($a, $b) => $a['name'] <=> $b['name']);
			}

			ksort($staff);
		}

		if (count($base['mappings']['nodes']) > 0)
		{
			$links = Kitsu::mappingsToUrls($base['mappings']['nodes'], "https://kitsu.io/anime/{$base['slug']}");
		}

		return AnimePage::from([
			'age_rating' => $base['ageRating'],
			'age_rating_guide' => $base['ageRatingGuide'],
			'characters' => $characters,
			'cover_image' => $base['posterImage']['views'][1]['url'],
			'episode_count' => $base['episodeCount'],
			'episode_length' => $base['episodeLength'],
			'genres' => $genres,
			'links' => $links,
			'id' => $base['id'],
			'slug' => $base['slug'],
			'staff' => $staff,
			'show_type' => $base['subtype'],
			'status' => Kitsu::getAiringStatus($base['startDate'], $base['endDate']),
			'streaming_links' => Kitsu::parseStreamingLinks($base['streamingLinks']['nodes'] ?? []),
			'synopsis' => $base['description']['en'],
			'title' => $title,
			'titles' => $titles,
			'titles_more' => $titles_more,
			'total_length' => $base['totalLength'],
			'trailer_id' => $base['youtubeTrailerVideoId'],
			'url' => "https://kitsu.io/anime/{$base['slug']}",
		]);
	}
}