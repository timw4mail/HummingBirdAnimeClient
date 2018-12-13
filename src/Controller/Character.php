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
use Aviat\AnimeClient\API\Kitsu\Transformer\CharacterTransformer;

use Aviat\Ion\Di\ContainerInterface;

/**
 * Controller for character description pages
 */
class Character extends BaseController {

	/**
	 * @var \Aviat\AnimeClient\API\Kitsu\Model
	 */
	private $model;

	/**
	 * Character constructor.
	 *
	 * @param ContainerInterface $container
	 * @throws \Aviat\Ion\Di\Exception\ContainerException
	 * @throws \Aviat\Ion\Di\Exception\NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->model = $container->get('kitsu-model');
	}

	/**
	 * Show information about a character
	 *
	 * @param string $slug
	 * @return void
	 */
	public function index(string $slug): void
	{
		$rawData = $this->model->getCharacter($slug);

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

		$data = (new CharacterTransformer())->transform($rawData)->toArray();

		$this->outputHTML('character/details', [
			'title' => $this->formatTitle(
				'Characters',
				$data['name']
			),
			'data' => $data,
		]);
	}
}