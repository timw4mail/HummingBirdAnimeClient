<?php

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
	 * Route mapping for main navigation
	 * @var array $nav_routes
	 */
	private $nav_routes = [
		'Watching' => '/',
		'Plan to Watch' => '/plan_to_watch',
		'On Hold' => '/on_hold',
		'Dropped' => '/dropped',
		'Completed' => '/completed',
		'Collection' => '/collection',
		'All' => '/all'
	];

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->model = new AnimeModel();
		$this->collection_model = new AnimeCollectionModel();
	}

	/**
	 * Show a portion, or all of the anime list
	 *
	 * @param string $type - The section of the list
	 * @param string $title - The title of the page
	 * @return void
	 */
	public function anime_list($type, $title)
	{
		$data = ($type != 'all')
			? $this->model->get_list($type)
			: $this->model->get_all_lists();

		$this->outputHTML('anime/list', [
			'title' => $title,
			'nav_routes' => $this->nav_routes,
			'sections' => $data
		]);
	}

	/**
	 * Show the anime collection page
	 *
	 * @return void
	 */
	public function collection()
	{
		$data = $this->collection_model->get_collection();

		$this->outputHTML('anime/collection', [
			'title' => WHOSE . " Anime Collection",
			'nav_routes' => $this->nav_routes,
			'sections' => $data
		]);
	}
}
// End of AnimeController.php