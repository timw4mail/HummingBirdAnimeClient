<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\Hummingbird\Enum\AnimeWatchingStatus;
use Aviat\AnimeClient\Model\Anime as AnimeModel;

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
	 * Data to ve sent to all routes in this controller
	 * @var array $base_data
	 */
	protected $base_data;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->model = $container->get('anime-model');
		$this->base_data = array_merge($this->base_data, [
			'menu_name' => 'anime_list',
			'message' => '',
			'url_type' => 'anime',
			'other_type' => 'manga',
			'config' => $this->config,
		]);
	}

	/**
	 * Show a portion, or all of the anime list
	 *
	 * @param string $type - The section of the list
	 * @param string $view - List or cover view
	 * @return void
	 */
	public function index($type = "watching", $view = '')
	{
		$type_title_map = [
			'all' => 'All',
			'watching' => 'Currently Watching',
			'plan_to_watch' => 'Plan to Watch',
			'on_hold' => 'On Hold',
			'dropped' => 'Dropped',
			'completed' => 'Completed'
		];

		$model_map = [
			'watching' => AnimeWatchingStatus::WATCHING,
			'plan_to_watch' => AnimeWatchingStatus::PLAN_TO_WATCH,
			'on_hold' => AnimeWatchingStatus::ON_HOLD,
			'all' => 'all',
			'dropped' => AnimeWatchingStatus::DROPPED,
			'completed' => AnimeWatchingStatus::COMPLETED
		];

		if (array_key_exists($type, $type_title_map))
		{
			$title = $this->config->get('whose_list') .
				"'s Anime List &middot; {$type_title_map[$type]}";
		}
		else
		{
			$title = '';
		}

		$view_map = [
			'' => 'cover',
			'list' => 'list'
		];

		$data = ($type != 'all')
			? $this->model->get_list($model_map[$type])
			: $this->model->get_all_lists();

		$this->outputHTML('anime/' . $view_map[$view], [
			'title' => $title,
			'sections' => $data
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
	 * Update an anime item
	 *
	 * @return boolean|null
	 */
	public function update()
	{
		$this->outputJSON($this->model->update($this->request->post->get()));
	}
}
// End of AnimeController.php