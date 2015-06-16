<?php
/**
 * Manga Controller
 */

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
		'Reading' => '/reading{/view}',
		'Plan to Read' => '/plan_to_read{/view}',
		'On Hold' => '/on_hold{/view}',
		'Dropped' => '/dropped{/view}',
		'Completed' => '/completed{/view}',
		'All' => '/all{/view}'
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
	 * @param string $view
	 * @return void
	 */
	public function manga_list($status, $title, $view)
	{
		$view_map = [
			'' => 'cover',
			'list' => 'list'
		];

		$data = ($status !== 'all')
			? [$status => $this->model->get_list($status)]
			: $this->model->get_all_lists();

		$this->outputHTML('manga/' . $view_map[$view], [
			'url_type' => 'manga',
			'other_type' => 'anime',
			'title' => $title,
			'nav_routes' => $this->nav_routes,
			'sections' => $data
		]);
	}
}
// End of MangaController.php