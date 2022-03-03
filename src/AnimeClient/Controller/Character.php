<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\API\Kitsu\Model;
use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\API\Kitsu\Transformer\CharacterTransformer;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;

/**
 * Controller for character description pages
 */
final class Character extends BaseController {

	private Model $model;

	/**
	 * Character constructor.
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->model = $container->get('kitsu-model');
	}

	/**
	 * Show information about a character
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