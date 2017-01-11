<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller;
use Aviat\AnimeClient\API\Kitsu;
use Aviat\AnimeClient\API\Kitsu\Enum\MangaReadingStatus;
use Aviat\AnimeClient\API\Kitsu\Transformer\MangaListTransformer;
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
	 * @var array $base_data
	 */
	protected $base_data;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->model = $container->get('manga-model');
		$this->base_data = array_merge($this->base_data, [
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
		$map = [
			'all' => 'All',
			'plan_to_read' => MangaModel::PLAN_TO_READ,
			'reading' => MangaModel::READING,
			'completed' => MangaModel::COMPLETED,
			'dropped' => MangaModel::DROPPED,
			'on_hold' => MangaModel::ON_HOLD
		];

		$title = $this->config->get('whose_list') . "'s Manga List &middot; {$map[$status]}";

		$view_map = [
			'' => 'cover',
			'list' => 'list'
		];

		$data = ($status !== 'all')
			? [$map[$status] => $this->model->getList($map[$status]) ]
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
	public function add_form()
	{
		$raw_status_list = MangaReadingStatus::getConstList();

		$statuses = [];

		foreach ($raw_status_list as $status_item)
		{
			$statuses[$status_item] = (string)$this->string($status_item)
				->underscored()
				->humanize()
				->titleize();
		}

		$this->set_session_redirect();
		$this->outputHTML('manga/add', [
			'title' => $this->config->get('whose_list') .
				"'s Manga List &middot; Add",
			'action_url' => $this->urlGenerator->url('manga/add'),
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
			$this->set_flash_message('Added new manga to list', 'success');
			// $this->cache->purge();
		}
		else
		{
			$this->set_flash_message('Failed to add new manga to list' . $result['body'], 'error');
		}

		$this->session_redirect();
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
		$this->set_session_redirect();
		$item = $this->model->getLibraryItem($id);
		$title = $this->config->get('whose_list') . "'s Manga List &middot; Edit";

		$this->outputHTML('manga/edit', [
			'title' => $title,
			'status_list' => Kitsu::getStatusToMangaSelectMap(),
			'item' => $item,
			'action' => $this->container->get('url-generator')
				->url('/manga/update_form'),
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
	public function form_update()
	{
		$data = $this->request->getParsedBody();

		// Do some minor data manipulation for
		// large form-based updates
		$transformer = new MangaListTransformer();
		$post_data = $transformer->untransform($data);
		$full_result = $this->model->updateLibraryItem($post_data);

		if ($full_result['statusCode'] === 200)
		{
			$this->set_flash_message("Successfully updated manga.", 'success');
			// $this->cache->purge();
		}
		else
		{
			$this->set_flash_message('Failed to update manga.', 'error');

		}

		$this->session_redirect();
	}

	/**
	 * Update a manga item
	 *
	 * @return void
	 */
	public function update()
	{
		if ($this->request->getHeader('content-type')[0] === 'application/json')
		{
			$data = JSON::decode((string)$this->request->getBody());
		}
		else
		{
			$data = $this->request->getParsedBody();
		}

		$response = $this->model->updateLibraryItem($data);

		// $this->cache->purge();
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
		$response = $this->model->deleteLibraryItem($id);

		if ($response)
		{
			$this->set_flash_message("Successfully deleted manga.", 'success');
			//$this->cache->purge();
		}
		else
		{
			$this->set_flash_message('Failed to delete manga.', 'error');
		}

		$this->session_redirect();
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

		$this->outputHTML('manga/details', [
			'title' => 'Manga &middot; ' . $data['title'],
			'data' => $data,
		]);
	}
}
// End of MangaController.php