<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\Hummingbird\Enum\AnimeWatchingStatus;
use Aviat\AnimeClient\Hummingbird\Transformer\AnimeListTransformer;

/**
 * Controller for Anime-related pages
 */
class Anime extends BaseController {

	use \Aviat\Ion\StringWrapper;

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
	 * Data cache
	 */
	protected $cache;

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
			'url_type' => 'anime',
			'other_type' => 'manga',
			'config' => $this->config,
		]);

		$this->cache = $container->get('cache');
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

		$title = (array_key_exists($type, $type_title_map))
			? $this->config->get('whose_list') .
				"'s Anime List &middot; {$type_title_map[$type]}"
			: '';

		$view_map = [
			'' => 'cover',
			'list' => 'list'
		];

		$data = ($type != 'all')
			? $this->cache->get($this->model, 'get_list', ['status' => $model_map[$type]])
			: $this->cache->get($this->model, 'get_all_lists', []);

		$this->outputHTML('anime/' . $view_map[$view], [
			'title' => $title,
			'sections' => $data
		]);
	}

	/**
	 * Form to add an anime
	 *
	 * @return void
	 */
	public function add_form()
	{
		$raw_status_list = AnimeWatchingStatus::getConstList();

		$statuses = [];

		foreach ($raw_status_list as $status_item)
		{
			$statuses[$status_item] = (string) $this->string($status_item)
				->underscored()
				->humanize()
				->titleize();
		}

		$this->set_session_redirect();
		$this->outputHTML('anime/add', [
			'title' => $this->config->get('whose_list') .
				"'s Anime List &middot; Add",
			'action_url' => $this->urlGenerator->url('anime/add'),
			'status_list' => $statuses
		]);
	}

	/**
	 * Add an anime to the list
	 *
	 * @return void
	 */
	public function add()
	{
		$data = $this->request->getParsedBody();
		if ( ! array_key_exists('id', $data))
		{
			$this->redirect("anime/add", 303);
		}

		$result = $this->model->update($data);

		if (intval($result['statusCode']) === 201)
		{
			$this->set_flash_message('Added new anime to list', 'success');
			$this->cache->purge();
		}
		else
		{
			$this->set_flash_message('Failed to add new anime to list', 'error');
		}

		$this->session_redirect();
	}

	/**
	 * Form to edit details about a series
	 *
	 * @param int $id
	 * @param string $status
	 * @return void
	 */
	public function edit($id, $status = "all")
	{
		$item = $this->model->get_library_item($id, $status);
		$raw_status_list = AnimeWatchingStatus::getConstList();

		$statuses = [];

		foreach ($raw_status_list as $status_item)
		{
			$statuses[$status_item] = (string) $this->string($status_item)
				->underscored()
				->humanize()
				->titleize();
		}

		$this->set_session_redirect();

		$this->outputHTML('anime/edit', [
			'title' => $this->config->get('whose_list') .
				"'s Anime List &middot; Edit",
			'item' => $item,
			'statuses' => $statuses,
			'action' => $this->container->get('url-generator')
				->url('/anime/update_form'),
		]);
	}

	/**
	 * Search for anime
	 *
	 * @return void
	 */
	public function search()
	{
		$queryParams = $this->request->getQueryParams();
		$query = $queryParams['query'];
		$this->outputJSON($this->model->search($query));
	}

	/**
	 * Update an anime item via a form submission
	 *
	 * @return void
	 */
	public function form_update()
	{
		$data = $this->request->getParsedBody();

		// Do some minor data manipulation for
		// large form-based updates
		$transformer = new AnimeListTransformer();
		$post_data = $transformer->untransform($data);

		$full_result = $this->model->update($post_data);
		$result = $full_result['body'];

		if (array_key_exists('anime', $result))
		{
			$title = ( ! empty($result['anime']['alternate_title']))
				? "{$result['anime']['title']} ({$result['anime']['alternate_title']})"
				: "{$result['anime']['title']}";

			$this->set_flash_message("Successfully updated {$title}.", 'success');
			$this->cache->purge();
		}
		else
		{
			$this->set_flash_message('Failed to update anime.', 'error');
		}

		$this->session_redirect();
	}

	/**
	 * Update an anime item
	 */
	public function update()
	{
		$response = $this->model->update($this->request->getParsedBody());
		$this->cache->purge();
		$this->outputJSON($response['body'], $response['statusCode']);
	}

	/**
	 * Remove an anime from the list
	 */
	public function delete()
	{
		$response = $this->model->delete($this->request->getParsedBody());

		if ($response['body'] == TRUE)
		{
			$this->set_flash_message("Successfully deleted anime.", 'success');
			$this->cache->purge();
		}
		else
		{
			$this->set_flash_message('Failed to delete anime.', 'error');
		}

		$this->session_redirect();
	}

	/**
	 * View details of an anime
	 *
	 * @param string $anime_id
	 * @return void
	 */
	public function details($anime_id)
	{
		$data = $this->model->get_anime($anime_id);

		$this->outputHTML('anime/details', [
			'title' => 'Anime &middot ' . $data['title'],
			'data' => $data,
		]);
	}

}
// End of AnimeController.php