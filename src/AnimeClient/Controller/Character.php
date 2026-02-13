<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\API\Kitsu\Model;
use Aviat\AnimeClient\API\Kitsu\Transformer\CharacterTransformer;
use Aviat\AnimeClient\Controller as BaseController;
use Aviat\Ion\Attribute\Controller;
use Aviat\Ion\Attribute\Route;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;

/**
 * Controller for character description pages
 */
#[Controller]
final class Character extends BaseController
{
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
	#[Route('character', '/character/{slug}')]
	public function index(string $slug): void
	{
		$rawData = $this->model->getCharacter($slug);
		$maybeCharacter = $rawData['data']['findCharacterBySlug'] ?? [];

		if ($maybeCharacter === [])
		{
			$this->notFound(
				$this->formatTitle(
					'Characters',
					'Character not found',
				),
				'Character Not Found',
			);
		}

		$data = new CharacterTransformer()
			->transform($rawData)
			->toArray();

		$this->outputHTML('character/details', [
			'title' => $this->formatTitle(
				'Characters',
				$data['name'],
			),
			'data' => $data,
		]);
	}
}
