<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aura\Router\Exception\RouteNotFound;
use Aviat\AnimeClient\Controller;
use Aviat\AnimeClient\API\Kitsu\Transformer\MangaListTransformer;
use Aviat\AnimeClient\API\Mapping\MangaReadingStatus;
use Aviat\AnimeClient\Model\Manga as MangaModel;
use Aviat\AnimeClient\Types\FormItem;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use Aviat\Ion\Json;

use InvalidArgumentException;
use Throwable;

/**
 * Controller for manga list
 */
final class Manga extends Controller {

	/**
	 * The manga model
	 * @var MangaModel $model
	 */
	protected MangaModel $model;

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

		$this->model = $container->get('manga-model');
		$this->baseData = array_merge($this->baseData, [
			'menu_name' => 'manga_list',
			'other_type' => 'anime',
			'url_type' => 'manga',
		]);
	}

	/**
	 * Get a section of the manga list
	 *
	 * @param string $status
	 * @param string $view
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function index($status = 'all', $view = ''): void
	{
		if ( ! in_array($status, [
			'all',
			'reading',
			'plan_to_read',
			'dropped',
			'on_hold',
			'completed',
		], TRUE))
		{
			$this->errorPage(404, 'Not Found', 'Page not found');
		}

		$statusTitle = MangaReadingStatus::ROUTE_TO_TITLE[$status];

		$title = $this->formatTitle(
			$this->config->get('whose_list') . "'s Manga List",
			$statusTitle
		);

		$view_map = [
			'' => 'cover',
			'list' => 'list'
		];

		$data = ($status !== 'all')
			? [ $statusTitle => $this->model->getList($statusTitle) ]
			: $this->model->getList('All');

		$this->outputHTML('manga/' . $view_map[$view], [
			'title' => $title,
			'sections' => $data,
		]);
	}

	/**
	 * Form to add an manga
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws RouteNotFound
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function addForm(): void
	{
		$this->checkAuth();

		$statuses = MangaReadingStatus::KITSU_TO_TITLE;

		$this->setSessionRedirect();
		$this->outputHTML('manga/add', [
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Manga List",
				'Add'
			),
			'action_url' => $this->url->generate('manga.add.post'),
			'status_list' => $statuses
		]);
	}

	/**
	 * Add an manga to the list
	 *
	 * @return void
	 * @throws NotFoundException
	 * @throws Throwable
	 * @throws ContainerException
	 */
	public function add(): void
	{
		$this->checkAuth();

		$data = $this->request->getParsedBody();
		if ( ! array_key_exists('id', $data))
		{
			$this->redirect('manga/add', 303);
		}

		if (empty($data['mal_id']))
		{
			unset($data['mal_id']);
		}

		$result = $this->model->createLibraryItem($data);

		if ($result)
		{
			$this->setFlashMessage('Added new manga to list', 'success');
			$this->cache->clear();
		}
		else
		{
			$this->setFlashMessage('Failed to add new manga to list' . $result['body'], 'error');
		}

		$this->sessionRedirect();
	}

	/**
	 * Show the manga edit form
	 *
	 * @param string $id
	 * @param string $status
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws RouteNotFound
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function edit($id, $status = 'All'): void
	{
		$this->checkAuth();

		$this->setSessionRedirect();
		$item = $this->model->getLibraryItem($id);
		$title = $this->formatTitle(
			$this->config->get('whose_list') . "'s Manga List",
			'Edit'
		);

		$this->outputHTML('manga/edit', [
			'title' => $title,
			'status_list' => MangaReadingStatus::KITSU_TO_TITLE,
			'item' => $item,
			'action' => $this->url->generate('update.post', [
				'controller' => 'manga'
			]),
		]);
	}

	/**
	 * Search for a manga to add to the list
	 *
	 * @return void
	 */
	public function search(): void
	{
		$queryParams = $this->request->getQueryParams();
		$query = $queryParams['query'];
		$this->outputJSON($this->model->search($query), 200);
	}

	/**
	 * Update an manga item via a form submission
	 *
	 * @return void
	 * @throws Throwable
	 * @throws NotFoundException
	 * @throws ContainerException
	 */
	public function formUpdate(): void
	{
		$this->checkAuth();

		$data = $this->request->getParsedBody();

		// Do some minor data manipulation for
		// large form-based updates
		$transformer = new MangaListTransformer();
		$post_data = $transformer->untransform($data);
		$full_result = $this->model->updateLibraryItem(FormItem::from($post_data));

		if ($full_result['statusCode'] === 200)
		{
			$this->setFlashMessage('Successfully updated manga.', 'success');
			$this->cache->clear();
		}
		else
		{
			$this->setFlashMessage('Failed to update manga.', 'error');

		}

		$this->sessionRedirect();
	}

	/**
	 * Increment the progress of a manga item
	 * @throws Throwable
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

		[$body, $statusCode] = $this->model->incrementLibraryItem(FormItem::from($data));

		$this->cache->clear();
		$this->outputJSON($body, $statusCode);
	}

	/**
	 * Remove an manga from the list
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws Throwable
	 * @return void
	 */
	public function delete(): void
	{
		$this->checkAuth();

		$body = $this->request->getParsedBody();
		$response = $this->model->deleteLibraryItem($body['id'], $body['mal_id']);

		if ($response)
		{
			$this->setFlashMessage('Successfully deleted manga.', 'success');
			$this->cache->clear();
		}
		else
		{
			$this->setFlashMessage('Failed to delete manga.', 'error');
		}

		$this->sessionRedirect();
	}

	/**
	 * View details of an manga
	 *
	 * @param string $manga_id
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 * @return void
	 */
	public function details($manga_id): void
	{
		$data = $this->model->getManga($manga_id);
		$staff = [];
		$characters = [];

		if ($data->isEmpty())
		{
			$this->notFound(
				$this->config->get('whose_list') .
					"'s Manga List &middot; Manga &middot; " .
					'Manga not found',
				'Manga Not Found'
			);
			return;
		}

		$this->outputHTML('manga/details', [
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Manga List",
				'Manga',
				$data['title']
			),
			'characters' => $characters,
			'data' => $data,
			'staff' => $staff,
		]);
	}
}
// End of MangaController.php
