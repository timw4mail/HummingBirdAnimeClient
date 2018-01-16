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
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller;
use Aviat\AnimeClient\API\Kitsu\Transformer\MangaListTransformer;
use Aviat\AnimeClient\API\Mapping\MangaReadingStatus;
use Aviat\AnimeClient\Model\Manga as MangaModel;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\{Json, StringWrapper};

/**
 * Controller for manga list
 */
class Manga extends Controller {

	use StringWrapper;

	/**
	 * The manga model
	 * @var MangaModel $model
	 */
	protected $model;

	/**
	 * Data to ve sent to all routes in this controller
	 * @var array $baseData
	 */
	protected $baseData;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->model = $container->get('manga-model');
		$this->baseData = array_merge($this->baseData, [
			'menu_name' => 'manga_list',
			'config' => $this->config,
			'url_type' => 'manga',
			'other_type' => 'anime'
		]);
	}

	/**
	 * Get a section of the manga list
	 *
	 * @param string $status
	 * @param string $view
	 * @return void
	 */
	public function index($status = "all", $view = "")
	{
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
	 * @return void
	 */
	public function addForm()
	{
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
	 */
	public function add()
	{
		$data = $this->request->getParsedBody();
		if ( ! array_key_exists('id', $data))
		{
			$this->redirect("manga/add", 303);
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
	 * @return void
	 */
	public function edit($id, $status = "All")
	{
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
	public function search()
	{
		$query_data = $this->request->getQueryParams();
		$this->outputJSON($this->model->search($query_data['query']));
	}

	/**
	 * Update an manga item via a form submission
	 *
	 * @return void
	 */
	public function formUpdate()
	{
		$data = $this->request->getParsedBody();

		// Do some minor data manipulation for
		// large form-based updates
		$transformer = new MangaListTransformer();
		$post_data = $transformer->untransform($data);
		$full_result = $this->model->updateLibraryItem($post_data);

		if ($full_result['statusCode'] === 200)
		{
			$this->setFlashMessage("Successfully updated manga.", 'success');
			$this->cache->clear();
		}
		else
		{
			$this->setFlashMessage('Failed to update manga.', 'error');

		}

		$this->sessionRedirect();
	}

	/**
	 * Update a manga item
	 *
	 * @return void
	 */
	public function update()
	{
		if (stripos($this->request->getHeader('content-type')[0], 'application/json') !== FALSE)
		{
			$data = Json::decode((string)$this->request->getBody());
		}
		else
		{
			$data = $this->request->getParsedBody();
		}

		$response = $this->model->updateLibraryItem($data);

		$this->cache->clear();
		$this->outputJSON($response['body'], $response['statusCode']);
	}

	/**
	 * Remove an manga from the list
	 *
	 * @return void
	 */
	public function delete()
	{
		$body = $this->request->getParsedBody();
		$id = $body['id'];
		$malId = $body['mal_id'];
		$response = $this->model->deleteLibraryItem($id, $malId);

		if ($response)
		{
			$this->setFlashMessage("Successfully deleted manga.", 'success');
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
	 * @return void
	 */
	public function details($manga_id)
	{
		$data = $this->model->getManga($manga_id);
		$characters = [];

		if (empty($data))
		{
			$this->notFound(
				$this->config->get('whose_list') .
					"'s Manga List &middot; Manga &middot; " .
					'Manga not found',
				'Manga Not Found'
			);
			return;
		}

		foreach($data['included'] as $included)
		{
			if ($included['type'] === 'characters')
			{
				$characters[$included['id']] = $included['attributes'];
			}
		}

		$this->outputHTML('manga/details', [
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Manga List",
				'Manga',
				$data['title']
			),
			'characters' => $characters,
			'data' => $data,
		]);
	}
}
// End of MangaController.php