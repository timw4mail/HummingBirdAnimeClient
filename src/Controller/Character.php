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

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\API\JsonAPI;
use Aviat\Ion\ArrayWrapper;

/**
 * Controller for character description pages
 */
final class Character extends BaseController {

	use ArrayWrapper;

	/**
	 * Show information about a character
	 *
	 * @param string $slug
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function index(string $slug): void
	{
		$model = $this->container->get('kitsu-model');

		$rawData = $model->getCharacter($slug);

		if (( ! array_key_exists('data', $rawData)) || empty($rawData['data']))
		{
			$this->notFound(
				$this->formatTitle(
					'Characters',
					'Character not found'
				),
				'Character Not Found'
			);

			return;
		}

		$data = JsonAPI::organizeData($rawData);

		$viewData = [
			'title' => $this->formatTitle(
				'Characters',
				$data[0]['attributes']['name']
			),
			'data' => $data,
			'castCount' => 0,
			'castings' => []
		];

		if (array_key_exists('included', $data) && array_key_exists('castings', $data['included']))
		{
			$viewData['castings'] = $this->organizeCast($data['included']['castings']);
			$viewData['castCount'] = $this->getCastCount($viewData['castings']);
		}

		$this->outputHTML('character', $viewData);
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

	private function getCastCount(array $cast): int
	{
		$count = 0;

		foreach($cast as $role)
		{
			if (
				array_key_exists('attributes', $role) &&
				array_key_exists('role', $role['attributes']) &&
				$role['attributes']['role'] !== NULL
			) {
				$count++;
			}
		}

		return $count;
	}

	private function organizeCast(array $cast): array
	{
		$cast = $this->dedupeCast($cast);
		$output = [];

		foreach($cast as $id => $role)
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
				$person = current($role['relationships']['person']['people'])['attributes'];
				$name = $person['name'];
				$item = [
					'person' => $person,
					'series' => $role['relationships']['media']['anime']
				];

				$output[$roleName][$language][] = $item;
			}
			else
			{
				$output[$roleName][] = $role['relationships']['person']['people'];
			}
		}

		return $output;
	}
}