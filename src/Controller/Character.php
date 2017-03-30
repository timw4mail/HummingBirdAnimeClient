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

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller as BaseController;

/**
 * Controller for character description pages
 */
class Character extends BaseController {

	public function index(string $slug)
	{
		$model = $this->container->get('kitsu-model');

		$data = $model->getCharacter($slug);

		if (( ! array_key_exists('data', $data)) || empty($data['data']))
		{
			return $this->notFound(
				$this->formatTitle(
					'Characters',
					'Character not found'
				),
				'Character Not Found'
			);
		}

		$this->outputHTML('character', [
			'title' => $this->formatTitle(
				'Characters',
				$data['data'][0]['attributes']['name']
			),
			'data' => $data['data'][0]['attributes']
		]);
	}
}