<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
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
use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeListTransformer;
use Aviat\AnimeClient\API\Enum\AnimeWatchingStatus\Kitsu as KitsuWatchingStatus;
use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\Types\AnimeFormItem;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;
use Aviat\Ion\StringWrapper;

/**
 * Controller for Anime-related pages
 */
final class Anime extends BaseController {

	use StringWrapper;

	/**
	 * The anime list model
	 * @var \Aviat\AnimeClient\Model\Anime $model
	 */
	protected $model;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->model = $container->get('anime-model');

		$this->baseData = array_merge($this->baseData, [
			'menu_name' => 'anime_list',
			'url_type' => 'anime',
			'other_type' => 'manga',
			'config' => $this->config,
		]);

		$this->cache = $container->get('cache');
	}

	/**
	 * Show a portion, or all of the anime list
	 *
	 * @param string|int $type - The section of the list
	 * @param string $view - List or cover view
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function index($type = KitsuWatchingStatus::WATCHING, string $view = NULL): void
	{
		$title = array_key_exists($type, AnimeWatchingStatus::ROUTE_TO_TITLE)
			? $this->formatTitle(
				$this->config->get('whose_list') . "'s Anime List",
				AnimeWatchingStatus::ROUTE_TO_TITLE[$type]
			)
			: '';

		$viewMap = [
			'' => 'cover',
			'list' => 'list'
		];

		$data = ($type !== 'all')
			? $this->model->getList(AnimeWatchingStatus::ROUTE_TO_KITSU[$type])
			: $this->model->getAllLists();

		$this->outputHTML('anime/' . $viewMap[$view], [
			'title' => $title,
			'sections' => $data
		]);
	}

	/**
	 * Form to add an anime
	 *
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \Aura\Router\Exception\RouteNotFound
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function addForm(): void
	{
		$this->setSessionRedirect();
		$this->outputHTML('anime/add', [
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Anime List",
				'Add'
			),
			'action_url' => $this->url->generate('anime.add.post'),
			'status_list' => AnimeWatchingStatus::KITSU_TO_TITLE
		]);
	}

	/**
	 * Add an anime to the list
	 *
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return void
	 */
	public function add(): void
	{
		$data = $this->request->getParsedBody();

		if (empty($data['mal_id']))
		{
			unset($data['mal_id']);
		}

		if ( ! array_key_exists('id', $data))
		{
			$this->redirect('anime/add', 303);
		}

		$result = $this->model->createLibraryItem($data);

		if ($result)
		{
			$this->setFlashMessage('Added new anime to list', 'success');
			$this->cache->clear();
		}
		else
		{
			$this->setFlashMessage('Failed to add new anime to list', 'error');
		}

		$this->sessionRedirect();
	}

	/**
	 * Form to edit details about a series
	 *
	 * @param string $id
	 * @param string $status
	 */
	public function edit(string $id, $status = 'all'): void
	{
		$item = $this->model->getLibraryItem($id);
		$this->setSessionRedirect();

		$this->outputHTML('anime/edit', [
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Anime List",
				'Edit'
			),
			'item' => $item,
			'statuses' => AnimeWatchingStatus::KITSU_TO_TITLE,
			'action' => $this->url->generate('update.post', [
				'controller' => 'anime'
			]),
		]);
	}

	/**
	 * Search for anime
	 *
	 * @return void
	 */
	public function search(): void
	{
		$queryParams = $this->request->getQueryParams();
		$query = $queryParams['query'];
		$this->outputJSON($this->model->search($query));
	}

	/**
	 * Update an anime item via a form submission
	 *
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return void
	 */
	public function formUpdate(): void
	{
		$data = $this->request->getParsedBody();

		// Do some minor data manipulation for
		// large form-based updates
		$transformer = new AnimeListTransformer();
		$postData = $transformer->untransform($data);
		$fullResult = $this->model->updateLibraryItem(new AnimeFormItem($postData));

		if ($fullResult['statusCode'] === 200)
		{
			$this->setFlashMessage('Successfully updated.', 'success');
			$this->cache->clear();
		}
		else
		{
			$this->setFlashMessage('Failed to update anime.', 'error');
		}

		$this->sessionRedirect();
	}

	/**
	 * Increase the watched count for an anime item
	 *
	 * @return void
	 */
	public function increment(): void
	{
		if (stripos($this->request->getHeader('content-type')[0], 'application/json') !== FALSE)
		{
			$data = Json::decode((string)$this->request->getBody());
		}
		else
		{
			$data = $this->request->getParsedBody();
		}

		$response = $this->model->incrementLibraryItem(new AnimeFormItem($data));

		$this->cache->clear();
		$this->outputJSON($response['body'], $response['statusCode']);
	}

	/**
	 * Remove an anime from the list
	 *
	 * @return void
	 */
	public function delete(): void
	{
		$body = $this->request->getParsedBody();
		$response = $this->model->deleteLibraryItem($body['id'], $body['mal_id']);

		if ($response === TRUE)
		{
			$this->setFlashMessage('Successfully deleted anime.', 'success');
			$this->cache->clear();
		}
		else
		{
			$this->setFlashMessage('Failed to delete anime.', 'error');
		}

		$this->sessionRedirect();
	}

	/**
	 * View details of an anime
	 *
	 * @param string $animeId
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function details(string $animeId): void
	{
		$show_data = $this->model->getAnime($animeId);
		$characters = [];

		if ($show_data->title === '')
		{
			$this->notFound(
				$this->config->get('whose_list') .
					"'s Anime List &middot; Anime &middot; " .
					'Anime not found',
				'Anime Not Found'
			);

			return;
		}

		if (array_key_exists('characters', $show_data['included']))
		{
			foreach($show_data['included']['characters'] as $id => $character)
			{
				$characters[$id] = $character['attributes'];
			}
		}

		$this->outputHTML('anime/details', [
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Anime List",
				'Anime',
				$show_data->title
			),
			'characters' => $characters,
			'show_data' => $show_data,
		]);
	}

	/**
	 * Find anime matching the selected genre
	 *
	 * @param string $genre
	 */
	public function genre(string $genre): void
	{
		// @TODO: implement
	}
}
// End of AnimeController.php