<?php
/**
 * Anime Controller
 */

namespace AnimeClient\Controller;

use AnimeClient\Base\Container;
use AnimeClient\Base\Controller as BaseController;
use AnimeClient\Base\Config;
use AnimeClient\Model\Anime as AnimeModel;
use AnimeClient\Model\AnimeCollection as AnimeCollectionModel;

/**
 * Controller for Anime-related pages
 */
class Anime extends BaseController {

	/**
	 * The anime list model
	 * @var object $model
	 */
	protected $model;

	/**
	 * The anime collection model
	 * @var object $collection_model
	 */
	private $collection_model;

	/**
	 * Data to ve sent to all routes in this controller
	 * @var array $base_data
	 */
	protected $base_data;

	/**
	 * Route mapping for main navigation
	 * @var array $nav_routes
	 */
	private $nav_routes = [
		'Watching' => '/anime/watching{/view}',
		'Plan to Watch' => '/anime/plan_to_watch{/view}',
		'On Hold' => '/anime/on_hold{/view}',
		'Dropped' => '/anime/dropped{/view}',
		'Completed' => '/anime/completed{/view}',
		'Collection' => '/collection/view{/view}',
		'All' => '/anime/all{/view}'
	];

	/**
	 * Constructor
	 */
	public function __construct(Container $container)
	{
		parent::__construct($container);

		$config = $container->get('config');

		if ($this->config->show_anime_collection === FALSE)
		{
			unset($this->nav_routes['Collection']);
		}

		$this->model = new AnimeModel($container);
		$this->collection_model = new AnimeCollectionModel($container);
		$this->base_data = [
			'message' => '',
			'url_type' => 'anime',
			'other_type' => 'manga',
			'nav_routes' => $this->nav_routes,
			'config' => $this->config,
		];
	}

	/**
	 * Search for anime
	 *
	 * @return void
	 */
	public function search()
	{
		$query = $this->request->query->get('query');
		$this->outputJSON($this->model->search($query));
	}

	/**
	 * Show a portion, or all of the anime list
	 *
	 * @param string $type - The section of the list
	 * @param string $title - The title of the page
	 * @return void
	 */
	public function anime_list($type, $title, $view)
	{
		$view_map = [
			'' => 'cover',
			'list' => 'list'
		];

		$data = ($type != 'all')
			? $this->model->get_list($type)
			: $this->model->get_all_lists();

		$this->outputHTML('anime/' . $view_map[$view], [
			'title' => $title,
			'sections' => $data
		]);
	}

	/**
	 * Show the anime collection page
	 *
	 * @return void
	 */
	public function collection($view)
	{
		$view_map = [
			'' => 'collection',
			'list' => 'collection_list'
		];

		$data = $this->collection_model->get_collection();

		$this->outputHTML('anime/' . $view_map[$view], [
			'title' => WHOSE . " Anime Collection",
			'sections' => $data,
			'genres' => $this->collection_model->get_genre_list()
		]);
	}

	/**
	 * Show the anime collection add/edit form
	 *
	 * @param int $id
	 * @return void
	 */
	public function collection_form($id=NULL)
	{
		$action = (is_null($id)) ? "Add" : "Edit";

		$this->outputHTML('anime/collection_' . strtolower($action), [
			'action' => $action,
			'action_url' => $this->config->full_url("collection/" . strtolower($action)),
			'title' => WHOSE . " Anime Collection &middot; {$action}",
			'media_items' => $this->collection_model->get_media_type_list(),
			'item' => ($action === "Edit") ? $this->collection_model->get($id) : []
		]);
	}

	/**
	 * Update a collection item
	 *
	 * @return void
	 */
	public function collection_edit()
	{
		$data = $this->request->post->get();
		if ( ! array_key_exists('hummingbird_id', $data))
		{
			$this->redirect("collection/view", 303, "anime");
		}

		$this->collection_model->update($data);

		$this->redirect("collection/view", 303, "anime");
	}

	/**
	 * Add a collection item
	 *
	 * @return void
	 */
	public function collection_add()
	{
		$data = $this->request->post->get();
		if ( ! array_key_exists('id', $data))
		{
			$this->redirect("collection/view", 303, "anime");
		}

		$this->collection_model->add($data);

		$this->redirect("collection/view", 303, "anime");
	}

	/**
	 * Update an anime item
	 *
	 * @return bool
	 */
	public function update()
	{
		$this->outputJSON($this->model->update($this->request->post->get()));
	}
}
// End of AnimeController.php
