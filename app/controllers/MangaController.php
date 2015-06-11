<?php

/**
 * Controller for manga list
 */
class MangaController extends BaseController {

	/**
	 * The manga model
	 * @var object $model
	 */
	private $model;

	/**
	 * Route mapping for main navigation
	 * @var array $nav_routes
	 */
	private $nav_routes = [
		'Reading' => '/',
		'Plan to Read' => '/plan_to_read',
		'On Hold' => '/on_hold',
		'Dropped' => '/dropped',
		'Completed' => '/completed',
		'All' => '/all'
	];

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->model = new MangaModel();
	}

	/**
	 * Get a section of the manga list
	 *
	 * @param string $status
	 * @param string $title
	 * @return void
	 */
	public function manga_list($status, $title)
	{
		$data = ($status !== 'all')
			? [$status => $this->model->get_list($status)]
			: $this->model->get_all_lists();

		$this->outputHTML('manga/list', [
			'title' => $title,
			'nav_routes' => $this->nav_routes,
			'sections' => $data
		]);
	}
}
// End of MangaController.php