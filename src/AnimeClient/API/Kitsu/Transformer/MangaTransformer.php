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
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu;
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
		$base = array_key_exists('findMangaBySlug', $item['data'])
			? $item['data']['findMangaBySlug']
			: $item['data']['findMangaById'];

		$characters = [];
		$staff = [];
		$genres = array_map(fn ($genre) => $genre['title']['en'], $base['categories']['nodes']);
		sort($genres);

		$title = $base['titles']['canonical'];
		$titles = Kitsu::getTitles($base['titles']);
		$titles_more = Kitsu::filterLocalizedTitles($base['titles']);

		if (count($base['characters']['nodes']) > 0)
		{
			$characters['main'] = [];
			$characters['supporting'] = [];

			foreach ($base['characters']['nodes'] as $rawCharacter)
			{
				$type = $rawCharacter['role'] === 'MAIN' ? 'main' : 'supporting';
				$details = $rawCharacter['character'];
				$characters[$type][$details['id']] = [
					'image' => $details['image'],
					'name' => $details['names']['canonical'],
					'slug' => $details['slug'],
				];
			}

			uasort($characters['main'], fn($a, $b) => $a['name'] <=> $b['name']);
			uasort($characters['supporting'], fn($a, $b) => $a['name'] <=> $b['name']);

			if (empty($characters['supporting']))
			{
				unset($characters['supporting']);
			}
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
					'slug' => $person['slug'],
					'name' => $name,
					'image' => [
						'original' => $person['image']['original']['url'],
					],
				];

				usort($staff[$role], fn ($a, $b) => $a['name'] <=> $b['name']);
			}

			ksort($staff);
		}

		$data = [
			'age_rating' => $base['ageRating'],
			'age_rating_guide' => $base['ageRatingGuide'],
			'characters' => $characters,
			'chapter_count' => $base['chapterCount'],
			'volume_count' => $base['volumeCount'],
			'cover_image' => $base['posterImage']['views'][1]['url'],
			'genres' => $genres,
			'manga_type' => $base['subtype'],
			'id' => $base['id'],
			'staff' => $staff,
			'status' => Kitsu::getPublishingStatus($base['status'], $base['startDate'], $base['endDate']),
			'synopsis' => $base['synopsis']['en'],
			'title' => $title,
			'titles' => $titles,
			'titles_more' => $titles_more,
			'url' => "https://kitsu.io/manga/{$base['slug']}",
		];

		return MangaPage::from($data);
	}
}