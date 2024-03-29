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
use Aviat\AnimeClient\Types\Character;

use Aviat\Ion\Transformer\AbstractTransformer;
use Locale;

/**
 * Data transformation class for character pages
 */
final class CharacterTransformer extends AbstractTransformer
{
	public function transform(array|object $item): Character
	{
		$item = (array) $item;
		$data = $item['data']['findCharacterBySlug'] ?? [];
		$castings = [];
		$media = [
			'anime' => [],
			'manga' => [],
		];

		$names = array_unique(
			[...[$data['names']['canonical']], ...array_values($data['names']['localized'])]
		);
		$name = array_shift($names);

		if (isset($data['media']['nodes']))
		{
			[$media, $castings] = $this->organizeMediaAndVoices($data['media']['nodes'] ?? []);
		}

		return Character::from([
			'castings' => $castings,
			'description' => $data['description']['en'],
			'id' => $data['id'],
			'image' => Kitsu::getImage($data),
			'media' => $media,
			'name' => $name,
			'names' => $names,
			'otherNames' => $data['names']['alternatives'],
		]);
	}

	/**
	 * @return array<int, mixed[]>
	 */
	protected function organizeMediaAndVoices(array $data): array
	{
		if (empty($data))
		{
			return [[], []];
		}

		$titleSort = static fn ($a, $b) => $a['title'] <=> $b['title'];

		// First, let's deal with related media
		$rawMedia = array_column($data, 'media');
		$rawAnime = array_filter($rawMedia, static fn ($item) => $item['type'] === 'Anime');
		$rawManga = array_filter($rawMedia, static fn ($item) => $item['type'] === 'Manga');

		$anime = array_map(static function ($item) {
			$output = $item;
			unset($output['titles']);
			$output['title'] = $item['titles']['canonical'];
			$output['titles'] = Kitsu::getFilteredTitles($item['titles']);

			return $output;
		}, $rawAnime);
		$manga = array_map(static function ($item) {
			$output = $item;
			unset($output['titles']);
			$output['title'] = $item['titles']['canonical'];
			$output['titles'] = Kitsu::getFilteredTitles($item['titles']);

			return $output;
		}, $rawManga);

		uasort($anime, $titleSort);
		uasort($manga, $titleSort);

		$media = [
			'anime' => $anime,
			'manga' => $manga,
		];

		// And now, reorganize voice actor relationships
		$rawVoices = array_filter($data, static fn ($item) => ( ! empty($item['voices'])) && (array) $item['voices']['nodes'] !== []);

		if (empty($rawVoices))
		{
			return [$media, []];
		}

		$castings = [
			'Voice Actor' => [],
		];

		foreach ($rawVoices as $voiceMap)
		{
			foreach ($voiceMap['voices']['nodes'] as $voice)
			{
				$lang = Locale::getDisplayLanguage($voice['locale'], 'en');
				$id = $voice['person']['name'];
				$seriesId = $voiceMap['media']['id'];

				if ( ! array_key_exists($lang, $castings['Voice Actor']))
				{
					$castings['Voice Actor'][$lang] = [];
				}

				if ( ! array_key_exists($id, $castings['Voice Actor'][$lang]))
				{
					$castings['Voice Actor'][$lang][$id] = [
						'person' => [
							'id' => $voice['person']['id'],
							'slug' => $voice['person']['slug'],
							'image' => Kitsu::getImage($voice['person']),
							'name' => $voice['person']['name'],
						],
						'series' => [],
					];
				}

				$castings['Voice Actor'][$lang][$id]['series'][$seriesId] = [
					'id' => $seriesId,
					'slug' => $voiceMap['media']['slug'],
					'title' => $voiceMap['media']['titles']['canonical'],
					'titles' => Kitsu::getFilteredTitles($voiceMap['media']['titles']),
					'posterImage' => Kitsu::getPosterImage($voiceMap['media']),
				];

				uasort($castings['Voice Actor'][$lang][$id]['series'], $titleSort);
				ksort($castings['Voice Actor'][$lang]);
			}
		}

		return [$media, $castings];
	}
}
