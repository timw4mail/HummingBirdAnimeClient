<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\JsonAPI;
use Aviat\AnimeClient\Types\Character;

use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Data transformation class for character pages
 */
final class CharacterTransformer extends AbstractTransformer {

	/**
	 * @param array $characterData
	 * @return Character
	 */
	public function transform($characterData): Character
	{
		$data = JsonAPI::organizeData($characterData);
		$attributes = $data[0]['attributes'];
		$castings = [];

		$names = array_unique(
			array_merge(
				[$attributes['canonicalName']],
				$attributes['names']
			)
		);
		$name = array_shift($names);

		if (array_key_exists('included', $data))
		{
			if (array_key_exists('anime', $data['included']))
			{
				uasort($data['included']['anime'], static function ($a, $b) {
					return $a['attributes']['canonicalTitle'] <=> $b['attributes']['canonicalTitle'];
				});
			}

			if (array_key_exists('manga', $data['included']))
			{
				uasort($data['included']['manga'], static function ($a, $b) {
					return $a['attributes']['canonicalTitle'] <=> $b['attributes']['canonicalTitle'];
				});
			}

			if (array_key_exists('castings', $data['included']))
			{
				$castings = $this->organizeCast($data['included']['castings']);
			}
		}

		return new Character([
			'castings' => $castings,
			'description' => $attributes['description'],
			'id' => $data[0]['id'],
			'media' => [
				'anime' => $data['included']['anime'] ?? [],
				'manga' => $data['included']['manga'] ?? [],
			],
			'name' => $name,
			'names' => $names,
			'otherNames' => $attributes['otherNames'],
		]);
	}

	/**
	 * Organize VA => anime relationships
	 *
	 * @param array $cast
	 * @return array
	 */
	private function dedupeCast(array $cast): array
	{
		$output = [];
		$people = [];

		$i = 0;
		foreach ($cast as &$role)
		{
			if (empty($role['attributes']['role']))
			{
				continue;
			}


			$person = current($role['relationships']['person']['people'])['attributes'];
			$hasName = array_key_exists($person['name'], $people);

			if ( ! $hasName)
			{
				$people[$person['name']] = $i;
				$role['relationships']['media']['anime'] = [current($role['relationships']['media']['anime'])];
				$output[$i] = $role;

				$i++;

				continue;
			}

			if (array_key_exists('anime', $role['relationships']['media']))
			{
				$key = $people[$person['name']];
				$output[$key]['relationships']['media']['anime'][] = current($role['relationships']['media']['anime']);
			}
			continue;
		}

		return $output;
	}

	protected function organizeCast(array $cast): array
	{
		$cast = $this->dedupeCast($cast);
		$output = [];

		foreach ($cast as $id => $role)
		{
			if (empty($role['attributes']['role']))
			{
				continue;
			}

			$language = $role['attributes']['language'];
			$roleName = $role['attributes']['role'];
			$isVA = $role['attributes']['voiceActor'];

			if ($isVA)
			{
				foreach ($role['relationships']['person']['people'] as $pid => $peoples)
				{
					$p = $peoples;

					$person = $p['attributes'];
					$person['id'] = $pid;
					$person['image'] = $person['image']['original'];

					uasort($role['relationships']['media']['anime'], static function ($a, $b) {
						return $a['attributes']['canonicalTitle'] <=> $b['attributes']['canonicalTitle'];
					});

					$item = [
						'person' => $person,
						'series' => $role['relationships']['media']['anime']
					];

					$output[$roleName][$language][] = $item;
				}
			}
			else
			{
				foreach ($role['relationships']['person']['people'] as $pid => $person)
				{
					$person['id'] = $pid;
					$output[$roleName][$pid] = $person;
				}
			}
		}

		return $output;
	}
}