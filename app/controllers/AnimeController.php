<?php
/**
 * Anime Controller
 */

/**
 * Controller for Anime-related pages
 */
class AnimeController extends BaseController {

	/**
	 * The anime list model
	 * @var object $model
	 */
	private $model;

	/**
	 * The anime collection model
	 * @var object $collection_model
	 */
	private $collection_model;

	/**
	 * Data to ve sent to all routes in this controller
	 * @var array $base_data
	 */
	private $base_data;

	/**
	 * Route mapping for main navigation
	 * @var array $nav_routes
	 */
	private $nav_routes = [
		'Watching' => '/watching{/view}',
		'Plan to Watch' => '/plan_to_watch{/view}',
		'On Hold' => '/on_hold{/view}',
		'Dropped' => '/dropped{/view}',
		'Completed' => '/completed{/view}',
		'Collection' => '/collection{/view}',
		'All' => '/all{/view}'
	];

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		if ($this->config->show_anime_collection === FALSE)
		{
			unset($this->nav_routes['Collection']);
		}

		$this->model = new AnimeModel();
		$this->collection_model = new AnimeCollectionModel();
		$this->base_data = [
			'url_type' => 'anime',
			'other_type' => 'manga',
			'nav_routes' => $this->nav_routes,
		];
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

		$this->outputHTML('anime/' . $view_map[$view], array_merge($this->base_data, [
			'title' => $title,
			'sections' => $data
		]));
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

		$this->outputHTML('anime/' . $view_map[$view], array_merge($this->base_data, [
			'title' => WHOSE . " Anime Collection",
			'sections' => $data
		]));
	}

	/**
	 * Show the login form
	 *
	 * @return void
	 */
	public function login()
	{
		$this->outputHTML('login', array_merge($this->base_data, [
			'title' => 'Api login'
		]));
	}

	/**
	 * Attempt to log in with the api
	 *
	 * @return void
	 */
	public function login_action()
	{
		if ($this->model->authenticate($this->config->hummingbird_username, $_POST['password']))
		{
			$this->redirect('');
		}
	}
}
// End of AnimeController.php