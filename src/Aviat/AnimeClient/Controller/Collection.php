<?php
/**
 * Anime Collection Controller
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Base\Container;
use Aviat\AnimeClient\Base\Controller as BaseController;
use Aviat\AnimeClient\Base\Config;
use Aviat\AnimeClient\Model\Anime as AnimeModel;
use Aviat\AnimeClient\Model\AnimeCollection as AnimeCollectionModel;

/**
 * Controller for Anime collection pages
 */
class Collection extends BaseController {

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
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		parent::__construct($container);

		if ($this->config->show_anime_collection === FALSE)
		{
			unset($this->nav_routes['Collection']);
		}

		$this->collection_model = new AnimeCollectionModel($container);
		$this->base_data = array_merge($this->base_data, [
			'message' => '',
			'url_type' => 'anime',
			'other_type' => 'manga',
			'nav_routes' => $this->nav_routes,
			'config' => $this->config,
		]);
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
	 * Show the anime collection page
	 *
	 * @param string $view
	 * @return void
	 */
	public function index($view)
	{
		$view_map = [
			'' => 'cover',
			'list' => 'list'
		];

		$data = $this->collection_model->get_collection();

		$this->outputHTML('collection/' . $view_map[$view], [
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
	public function form($id=NULL)
	{
		$action = (is_null($id)) ? "Add" : "Edit";

		$this->outputHTML('collection/'. strtolower($action), [
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
	public function edit()
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
	public function add()
	{
		$data = $this->request->post->get();
		if ( ! array_key_exists('id', $data))
		{
			$this->redirect("collection/view", 303, "anime");
		}

		$this->collection_model->add($data);

		$this->redirect("collection/view", 303, "anime");
	}
}
// End of CollectionController.php