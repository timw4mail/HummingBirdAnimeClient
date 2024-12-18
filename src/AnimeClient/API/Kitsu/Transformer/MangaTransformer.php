<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\Kitsu;
use Aviat\AnimeClient\Types\MangaPage;
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transformer for manga description page
 */
final class MangaTransformer extends AbstractTransformer
{
	/**
	 * Convert raw api response to a more
	 * logical and workable structure
	 *
	 * @param array|object $item API library item
	 */
	public function transform(array|object $item): MangaPage
	{
		$item = (array) $item;
		$base = $item['data']['findMangaBySlug'] ?? $item['data']['findMangaById'] ?? $item['data']['randomMedia'];
		$characters = [];
		$links = [];
		$staff = [];
		$genres = array_map(static fn ($genre) => $genre['title']['en'], $base['categories']['nodes']);
		sort($genres);

		$title = $base['titles']['canonical'];
		$titles = Kitsu::getTitles($base['titles']);
		$titles_more = Kitsu::filterLocalizedTitles($base['titles']);

		if ((is_countable($base['characters']['nodes']) ? count($base['characters']['nodes']) : 0) > 0)
		{
			foreach ($base['characters']['nodes'] as $rawCharacter)
			{
				$type = mb_strtolower($rawCharacter['role']);
				if ( ! isset($characters[$type]))
				{
					$characters[$type] = [];
				}

				$details = $rawCharacter['character'];
				if (array_key_exists($details['id'], (array) $characters[$type]))
				{
					$characters[$type][$details['id']] = [
						'image' => Kitsu::getImage($details),
						'name' => $details['names']['canonical'],
						'slug' => $details['slug'],
					];
				}
			}

			foreach (array_keys($characters) as $type)
			{
				if (empty($characters[$type]))
				{
					unset($characters[$type]);
				}
				else
				{
					uasort($characters[$type], static fn ($a, $b) => $a['name'] <=> $b['name']);
				}
			}

			krsort($characters);
		}

		if ((is_countable($base['staff']['nodes']) ? count($base['staff']['nodes']) : 0) > 0)
		{
			foreach ($base['staff']['nodes'] as $staffing)
			{
				$person = $staffing['person'];
				$role = $staffing['role'];
				$name = $person['names']['localized'][$person['names']['canonical']];

				// If this person object is so broken as to not have a proper image object,
				// just skip it. No point in showing a role with nothing in it.
				if ($person === NULL || $person['id'] === NULL || $person['image'] === NULL)
				{
					continue;
				}

				if ( ! array_key_exists($role, $staff))
				{
					$staff[$role] = [];
				}

				$staff[$role][$person['id']] = [
					'id' => $person['id'],
					'slug' => $person['slug'],
					'name' => $name,
					'image' => Kitsu::getImage($person),
				];

				usort($staff[$role], static fn ($a, $b) => $a['name'] <=> $b['name']);
			}

			ksort($staff);
		}

		if ((is_countable($base['mappings']['nodes']) ? count($base['mappings']['nodes']) : 0) > 0)
		{
			$links = Kitsu::mappingsToUrls($base['mappings']['nodes'], "https://kitsu.app/manga/{$base['slug']}");
		}

		$data = [
			'age_rating' => $base['ageRating'],
			'age_rating_guide' => $base['ageRatingGuide'],
			'characters' => $characters,
			'chapter_count' => $base['chapterCount'],
			'volume_count' => $base['volumeCount'],
			'cover_image' => Kitsu::getPosterImage($base),
			'genres' => $genres,
			'links' => $links,
			'manga_type' => $base['subtype'],
			'id' => $base['id'],
			'staff' => $staff,
			'status' => Kitsu::getPublishingStatus($base['status'], $base['startDate'], $base['endDate']),
			'synopsis' => $base['description']['en'],
			'title' => $title,
			'titles' => $titles,
			'titles_more' => $titles_more,
			'url' => "https://kitsu.app/manga/{$base['slug']}",
		];

		return MangaPage::from($data);
	}
}
