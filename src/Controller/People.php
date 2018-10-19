<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.1
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\API\JsonAPI;

/**
 * Controller for People pages
 */
final class People extends BaseController {
	/**
	 * Show information about a person
	 *
	 * @param string $id
	 * @return void
	 */
	public function index(string $id): void
	{
		$model = $this->container->get('kitsu-model');

		$rawData = $model->getPerson($id);

		if (( ! array_key_exists('data', $rawData)) || empty($rawData['data']))
		{
			$this->notFound(
				$this->formatTitle(
					'People',
					'Person not found'
				),
				'Person Not Found'
			);

			return;
		}

		$data = JsonAPI::organizeData($rawData);

		$viewData = [
			'title' => $this->formatTitle(
				'People',
				$data['attributes']['name']
			),
			'data' => $data,
			'castCount' => 0,
			'castings' => []
		];

		if (array_key_exists('included', $data) && array_key_exists('castings', $data['included']))
		{
			$viewData['castings'] = $this->organizeCast($data['included']['castings']);
			$viewData['castCount'] = count($viewData['castings']);
		}

		$this->outputHTML('person', $viewData);
	}

	protected function organizeCast(array $cast): array
	{
		$output = [];

		foreach ($cast as $id => $role)
		{
			if (empty($role['attributes']['role']))
			{
				continue;
			}

			$roleName = $role['attributes']['role'];
			$media = $role['relationships']['media'];

			if (array_key_exists('anime', $media))
			{
				foreach($media['anime'] as $sid => $series)
				{
					$output[$roleName]['anime'][$sid] = $series;
				}
				uasort($output[$roleName]['anime'], function ($a, $b) {
					return $a['attributes']['canonicalTitle'] <=> $b['attributes']['canonicalTitle'];
				});
			}
			else if (array_key_exists('manga', $media))
			{
				foreach ($media['manga'] as $sid => $series)
				{
					$output[$roleName]['manga'][$sid] = $series;
				}
				uasort($output[$roleName]['anime'], function ($a, $b) {
					return $a['attributes']['canonicalTitle'] <=> $b['attributes']['canonicalTitle'];
				});
			}
		}

		return $output;
	}
}