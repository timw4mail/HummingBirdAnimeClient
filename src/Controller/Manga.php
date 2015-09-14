<?php
/**
 * Manga Controller
 */
namespace AnimeClient\Controller;

use AnimeClient\Base\Container;
use AnimeClient\Base\Controller;
use AnimeClient\Base\Config;
use AnimeClient\Model\Manga as MangaModel;

/**
 * Controller for manga list
 */
class Manga extends Controller {

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
		'Reading' => '/manga/reading{/view}',
		'Plan to Read' => '/manga/plan_to_read{/view}',
		'On Hold' => '/manga/on_hold{/view}',
		'Dropped' => '/manga/dropped{/view}',
		'Completed' => '/manga/completed{/view}',
		'All' => '/manga/all{/view}'
	];

	/**
	 * Constructor
	 */
	public function __construct(Container $container)
	{
		parent::__construct($container);
		$config = $container->get('config');
		$this->model = new MangaModel($container);
		$this->base_data = [
			'config' => $this->config,
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
