<?php
/**
 * Anime Controller
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Container;
use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\Config;
use Aviat\AnimeClient\Model\Anime as AnimeModel;
use Aviat\AnimeClient\Model\AnimeCollection as AnimeCollectionModel;

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
