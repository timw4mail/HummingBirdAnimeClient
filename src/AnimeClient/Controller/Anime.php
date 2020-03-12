<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.3
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aura\Router\Exception\RouteNotFound;
use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeListTransformer;
use Aviat\AnimeClient\API\Enum\AnimeWatchingStatus\Kitsu as KitsuWatchingStatus;
use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\Types\FormItem;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;
use Aviat\Ion\Json;

use InvalidArgumentException;
use Throwable;

/**
 * Controller for Anime-related pages
 */
final class Anime extends BaseController {

	/**
	 * The anime list model
	 * @var \Aviat\AnimeClient\Model\Anime $model
	 */
	protected $model;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->model = $container->get('anime-model');

		$this->baseData = array_merge($this->baseData, [
			'menu_name' => 'anime_list',
			'other_type' => 'manga',
			'url_type' => 'anime',
		]);
	}

	/**
	 * Show a portion, or all of the anime list
	 *
	 * @param string|int $type - The section of the list
	 * @param string $view - List or cover view
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 * @return void
	 */
	public function index($type = KitsuWatchingStatus::WATCHING, string $view = NULL): void
	{
		if ( ! in_array($type, [
			'all',
			'watching',
			'plan_to_watch',
			'on_hold',
			'dropped',
			'completed',
		], TRUE))
		{
			$this->errorPage(404, 'Not Found', 'Page not found');
		}

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
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws RouteNotFound
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 * @return void
	 */
	public function addForm(): void
	{
		$this->checkAuth();

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
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws Throwable
	 * @return void
	 */
	public function add(): void
	{
		$this->checkAuth();

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
	 * @throws RouteNotFound
	 */
	public function edit(string $id, $status = 'all'): void
	{
		$this->checkAuth();

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
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws Throwable
	 * @return void
	 */
	public function formUpdate(): void
	{
		$this->checkAuth();

		$data = $this->request->getParsedBody();

		// Do some minor data manipulation for
		// large form-based updates
		$transformer = new AnimeListTransformer();
		$postData = $transformer->untransform($data);
		$fullResult = $this->model->updateLibraryItem(new FormItem($postData));

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
	 * @throws Throwable
	 * @return void
	 */
	public function increment(): void
	{
		$this->checkAuth();

		if (stripos($this->request->getHeader('content-type')[0], 'application/json') !== FALSE)
		{
			$data = Json::decode((string)$this->request->getBody());
		}
		else
		{
			$data = $this->request->getParsedBody();
		}

		if (empty($data))
		{
			$this->errorPage(400, 'Bad Request', '');
			die();
		}

		$response = $this->model->incrementLibraryItem(new FormItem($data));

		$this->cache->clear();
		$this->outputJSON($response['body'], $response['statusCode']);
	}

	/**
	 * Remove an anime from the list
	 *
	 * @throws Throwable
	 * @return void
	 */
	public function delete(): void
	{
		$this->checkAuth();

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
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function details(string $animeId): void
	{
		try
		{
			$data = $this->model->getAnime($animeId);

			if ($data->isEmpty())
			{
				$this->notFound(
					$this->config->get('whose_list') .
					"'s Anime List &middot; Anime &middot; " .
					'Anime not found',
					'Anime Not Found'
				);

				return;
			}

			$this->outputHTML('anime/details', [
				'title' => $this->formatTitle(
					$this->config->get('whose_list') . "'s Anime List",
					'Anime',
					$data->title
				),
				'data' => $data,
			]);
		}
		catch (\TypeError $e)
		{
			$this->notFound(
				$this->config->get('whose_list') .
				"'s Anime List &middot; Anime &middot; " .
				'Anime not found',
				'Anime Not Found'
			);
		}
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