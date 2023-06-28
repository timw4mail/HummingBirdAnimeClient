<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\API\Kitsu\Transformer\MangaListTransformer;
use Aviat\AnimeClient\API\Mapping\MangaReadingStatus;
use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\Model\Manga as MangaModel;
use Aviat\AnimeClient\Types\FormItem;
use Aviat\Ion\Attribute\{Controller, Route};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use Aviat\Ion\Json;

/**
 * Controller for manga list
 */
#[Controller('manga')]
final class Manga extends BaseController
{
	/**
	 * The manga model
	 */
	protected MangaModel $model;

	/**
	 * Constructor
	 *
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
	 */
	#[Route('manga.list', '/list/{status}{/view}')]
	public function index(string $status = 'all', ?string $view = ''): void
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
			'list' => 'list',
		];

		$data = ($status !== 'all')
			? [$statusTitle => $this->model->getList($statusTitle)]
			: $this->model->getList('All');

		$this->outputHTML('manga/' . $view_map[$view], [
			'title' => $title,
			'sections' => $data,
		]);
	}

	/**
	 * Form to add a manga
	 */
	#[Route('manga.add.get', '/manga/add')]
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
			'status_list' => $statuses,
		]);
	}

	/**
	 * Add a manga to the list
	 */
	#[Route('manage.add.post', '/manga/add', Route::POST)]
	public function add(): void
	{
		$this->checkAuth();

		$data = (array) $this->request->getParsedBody();
		if ( ! array_key_exists('id', $data))
		{
			$this->redirect('manga/add', 303);
		}

		if (empty($data['mal_id']))
		{
			unset($data['mal_id']);
		}

		$result = $this->model->createItem($data);

		if ($result)
		{
			$this->setFlashMessage('Added new manga to list', 'success');
			$this->cache->clear();
		}
		else
		{
			$this->setFlashMessage('Failed to add new manga to list:' . print_r($data, TRUE), 'error');
		}

		$this->sessionRedirect();
	}

	/**
	 * Show the manga edit form
	 */
	#[Route('manga.edit', '/manga/edit/{id}/{status}')]
	public function edit(string $id, string $status = 'All'): void
	{
		$this->checkAuth();

		$this->setSessionRedirect();
		$item = $this->model->getItem($id);
		$title = $this->formatTitle(
			$this->config->get('whose_list') . "'s Manga List",
			'Edit'
		);

		$this->outputHTML('manga/edit', [
			'title' => $title,
			'status_list' => MangaReadingStatus::KITSU_TO_TITLE,
			'item' => $item,
			'action' => $this->url->generate('update.post', [
				'controller' => 'manga',
			]),
		]);
	}

	/**
	 * Search for a manga to add to the list
	 */
	#[Route('manga.search', '/manga/search')]
	public function search(): void
	{
		$queryParams = $this->request->getQueryParams();
		$query = $queryParams['query'];
		$this->outputJSON($this->model->search($query), 200);
	}

	/**
	 * Update an manga item via a form submission
	 */
	#[Route('manga.update.post', '/manga/update', Route::POST)]
	public function formUpdate(): void
	{
		$this->checkAuth();

		$data = (array) $this->request->getParsedBody();

		// Do some minor data manipulation for
		// large form-based updates
		$transformer = new MangaListTransformer();
		$post_data = $transformer->untransform($data);
		$full_result = $this->model->updateItem(FormItem::from($post_data));

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
	 */
	#[Route('manga.increment', '/manga/increment', Route::POST)]
	public function increment(): void
	{
		$this->checkAuth();

		if (str_contains($this->request->getHeader('content-type')[0], 'application/json'))
		{
			$data = Json::decode((string) $this->request->getBody());
		}
		else
		{
			$data = $this->request->getParsedBody();
		}

		$res = $this->model->incrementItem(FormItem::from($data));
		$body = $res['body'];
		$statusCode = $res['statusCode'];

		$this->cache->clear();
		$this->outputJSON($body, $statusCode);
	}

	/**
	 * Remove an manga from the list
	 */
	#[Route('manga.delete', '/manga/delete', Route::POST)]
	public function delete(): void
	{
		$this->checkAuth();

		$body = (array) $this->request->getParsedBody();
		$response = $this->model->deleteItem(FormItem::from($body));

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
	 */
	#[Route('manga.details', '/manga/details/{id}')]
	public function details(string $id): void
	{
		$data = $this->model->getManga($id);
		if ($data->isEmpty())
		{
			$this->notFound(
				$this->config->get('whose_list') .
					"'s Manga List &middot; Manga &middot; " .
					'Manga not found',
				'Manga Not Found'
			);
		}

		$this->outputHTML('manga/details', [
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Manga List",
				'Manga',
				$data['title']
			),
			'data' => $data,
		]);
	}

	/**
	 * View details of a random manga
	 */
	#[Route('manga.random', '/manga/details/random')]
	public function random(): void
	{
		$data = $this->model->getRandomManga();
		if ($data->isEmpty())
		{
			$this->notFound(
				$this->config->get('whose_list') .
				"'s Manga List &middot; Manga &middot; " .
				'Manga not found',
				'Manga Not Found'
			);
		}

		$this->outputHTML('manga/details', [
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Manga List",
				'Manga',
				$data['title']
			),
			'data' => $data,
		]);
	}
}

// End of MangaController.php
