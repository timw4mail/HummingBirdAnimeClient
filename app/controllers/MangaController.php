<?php
/**
 * Manga Controller
 */
namespace AnimeClient;

/**
 * Controller for manga list
 */
class MangaController extends BaseController {

	/**
	 * The manga model
	 * @var object $model
	 */
	protected $model;

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
		$this->base_data = [
			'url_type' => 'manga',
			'other_type' => 'anime',
			'nav_routes' => $this->nav_routes
		];
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
			'title' => $title,
			'sections' => $data
		]);
	}
}
// End of MangaController.php