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
 * @copyright   2015 - 2019  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\API\Kitsu\Transformer\PersonTransformer;

use Aviat\Ion\Di\ContainerInterface;

/**
 * Controller for People pages
 */
final class People extends BaseController {

	/**
	 * @var \Aviat\AnimeClient\API\Kitsu\Model
	 */
	private $model;

	/**
	 * People constructor.
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
	 * Show information about a person
	 *
	 * @param string $id
	 * @return void
	 */
	public function index(string $id): void
	{
		$rawData = $this->model->getPerson($id);
		$data = (new PersonTransformer())->transform($rawData)->toArray();

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

		$this->outputHTML('person/details', [
			'title' => $this->formatTitle(
				'People',
				$data['name']
			),
			'data' => $data,
		]);
	}
}