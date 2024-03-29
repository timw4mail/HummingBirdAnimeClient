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
use Aviat\AnimeClient\Types\Person;
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Data transformation class for people pages
 */
final class PersonTransformer extends AbstractTransformer
{
	public function transform(array|object $item): Person
	{
		$item = (array) $item;
		$data = $item['data']['findPersonBySlug'] ?? [];
		$canonicalName = $data['names']['localized'][$data['names']['canonical']]
			?? array_shift($data['names']['localized']);

		$orgData = $this->organizeData($data);

		return Person::from([
			'id' => $data['id'],
			'name' => $canonicalName,
			'birthday' => $data['birthday'],
			'image' => Kitsu::getImage($data),
			'names' => array_diff($data['names']['localized'], [$canonicalName]),
			'description' => $data['description']['en'] ?? '',
			'characters' => $orgData['characters'],
			'staff' => $orgData['staff'],
		]);
	}

	/**
	 * @return array<string, array<int|string, array<int|string, array<int|string, array<int|string, mixed>>>>>
	 */
	protected function organizeData(array $data): array
	{
		$output = [
			'characters' => [],
			'staff' => [],
		];

		$characters = [];
		$staff = [];

		if ((is_countable($data['mediaStaff']['nodes']) ? count($data['mediaStaff']['nodes']) : 0) > 0)
		{
			$roles = array_unique(array_column($data['mediaStaff']['nodes'], 'role'));

			foreach ($roles as $role)
			{
				$staff[$role] = [];
			}

			ksort($staff);

			foreach ($data['mediaStaff']['nodes'] as $staffing)
			{
				if (empty($staffing['media']))
				{
					continue;
				}

				$media = $staffing['media'];
				$role = $staffing['role'];
				$title = $media['titles']['canonical'];
				$type = strtolower($media['type']);

				$staff[$role][$type][$media['id']] = [
					'id' => $media['id'],
					'title' => $title,
					'titles' => array_merge([$title], Kitsu::getFilteredTitles($media['titles'])),
					'image' => Kitsu::getPosterImage($media),
					'slug' => $media['slug'],
				];

				uasort($staff[$role][$type], static fn ($a, $b) => $a['title'] <=> $b['title']);
			}

			$output['staff'] = $staff;
		}

		if ((is_countable($data['voices']['nodes']) ? count($data['voices']['nodes']) : 0) > 0)
		{
			foreach ($data['voices']['nodes'] as $voicing)
			{
				if ($voicing === NULL)
				{
					continue;
				}

				$character = $voicing['mediaCharacter']['character'] ?? [];
				$charId = $character['id'];
				$rawMedia = $voicing['mediaCharacter']['media'];
				$role = strtolower($voicing['mediaCharacter']['role']);

				$media = [
					'id' => $rawMedia['id'],
					'slug' => $rawMedia['slug'],
					'image' => Kitsu::getPosterImage($rawMedia),
					'titles' => array_merge(
						[$rawMedia['titles']['canonical']],
						Kitsu::getFilteredTitles($rawMedia['titles']),
					),
				];

				if ( ! isset($characters[$role][$charId]))
				{
					if ( ! array_key_exists($role, $characters))
					{
						$characters[$role] = [];
					}

					$characters[$role][$charId] = [
						'character' => [
							'id' => $character['id'],
							'slug' => $character['slug'],
							'image' => Kitsu::getImage($character),
							'canonicalName' => $character['names']['canonical'],
						],
						'media' => [
							$media['id'] => $media,
						],
					];
				}
				else
				{
					$characters[$role][$charId]['media'][$media['id']] = $media;
				}
			}

			foreach ($characters as $role => $_)
			{
				// Sort the characters by name
				uasort(
					$characters[$role],
					static fn ($a, $b) => $a['character']['canonicalName'] <=> $b['character']['canonicalName']
				);

				// Sort the media for the character
				foreach ($characters[$role] as $charId => $__)
				{
					uasort(
						$characters[$role][$charId]['media'],
						static fn ($a, $b) => $a['titles'][0] <=> $b['titles'][0]
					);
				}
			}

			krsort($characters);

			$output['characters'] = $characters;
		}

		return $output;
	}
}
