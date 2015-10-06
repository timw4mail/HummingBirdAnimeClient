<?php
/**
 * Manga Controller
 */
namespace Aviat\AnimeClient\Controller;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Controller;
use Aviat\AnimeClient\Config;
use Aviat\AnimeClient\Model\Manga as MangaModel;

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
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$config = $container->get('config');
		$this->model = new MangaModel($container);
		$this->base_data = array_merge($this->base_data, [
			'config' => $this->config,
			'url_type' => 'manga',
			'other_type' => 'anime',
			'nav_routes' => $this->nav_routes
		]);
	}

	public function index($status = "all", $view = "")
	{
		return $this->manga_list($status, $view);
	}

	/**
	 * Update an anime item
	 *
	 * @return boolean|null
	 */
	public function update()
	{
		$this->outputJSON($this->model->update($this->request->post->get()));
	}

	/**
	 * Get a section of the manga list
	 *
	 * @param string $status
	 * @param string $view
	 * @return void
	 */
	protected function manga_list($status, $view)
	{
		$map = [
			'all' => 'All',
			'plan_to_read' => MangaModel::PLAN_TO_READ,
			'reading' => MangaModel::READING,
			'completed' => MangaModel::COMPLETED,
			'dropped' => MangaModel::DROPPED,
			'on_hold' => MangaModel::ON_HOLD
		];

		$title = $this->config->whose_list . "'s Manga List &middot; {$map[$status]}";

		$view_map = [
			'' => 'cover',
			'list' => 'list'
		];

		$data = ($status !== 'all')
			? [$map[$status] => $this->model->get_list($map[$status])]
			: $this->model->get_all_lists();

		$this->outputHTML('manga/' . $view_map[$view], [
			'title' => $title,
			'sections' => $data,
		]);
	}
}
// End of MangaController.php