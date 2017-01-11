<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\API\Kitsu;
use Aviat\AnimeClient\API\Kitsu\Enum\AnimeWatchingStatus;
use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeListTransformer;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;
use Aviat\Ion\StringWrapper;

/**
 * Controller for Anime-related pages
 */
class Anime extends BaseController {

	use StringWrapper;

	/**
	 * The anime list model
	 * @var object $model
	 */
	protected $model;

	/**
	 * Data to be sent to all routes in this controller
	 * @var array $base_data
	 */
	protected $base_data;

	/**
	 * Data cache
	 * @var Aviat\Ion\Cache\CacheInterface
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
	 * @param string|int $type - The section of the list
	 * @param string $view - List or cover view
	 * @return void
	 */
	public function index($type = AnimeWatchingStatus::WATCHING, string $view = NULL)
	{
		$type_title_map = [
			'all' => 'All',
			AnimeWatchingStatus::WATCHING => 'Currently Watching',
			AnimeWatchingStatus::PLAN_TO_WATCH => 'Plan to Watch',
			AnimeWatchingStatus::ON_HOLD => 'On Hold',
			AnimeWatchingStatus::DROPPED => 'Dropped',
			AnimeWatchingStatus::COMPLETED => 'Completed'
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

		$data = ($type !== 'all')
			? $this->model->getList($model_map[$type])
			: $this->model->get_all_lists();

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
		$statuses = [
			AnimeWatchingStatus::WATCHING => 'Currently Watching',
			AnimeWatchingStatus::PLAN_TO_WATCH => 'Plan to Watch',
			AnimeWatchingStatus::ON_HOLD => 'On Hold',
			AnimeWatchingStatus::DROPPED => 'Dropped',
			AnimeWatchingStatus::COMPLETED => 'Completed'
		];

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

		$result = $this->model->createLibraryItem($data);

		if ($result)
		{
			$this->set_flash_message('Added new anime to list', 'success');
			// $this->cache->purge();
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
		$item = $this->model->getLibraryItem($id, $status);
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
			'statuses' => Kitsu::getStatusToSelectMap(),
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
		$full_result = $this->model->updateLibraryItem($post_data);

		if ($full_result['statusCode'] === 200)
		{
			$this->set_flash_message("Successfully updated.", 'success');
			// $this->cache->purge();
		}
		else
		{
			$this->set_flash_message('Failed to update anime.', 'error');
		}

		$this->session_redirect();
	}

	/**
	 * Update an anime item
	 *
	 * @return void
	 */
	public function update()
	{
		if ($this->request->getHeader('content-type')[0] === 'application/json')
		{
			$data = JSON::decode((string)$this->request->getBody());
		}
		else
		{
			$data = $this->request->getParsedBody();
		}

		$response = $this->model->updateLibraryItem($data);

		// $this->cache->purge();
		$this->outputJSON($response['body'], $response['statusCode']);
	}

	/**
	 * Remove an anime from the list
	 *
	 * @return void
	 */
	public function delete()
	{
		$body = $this->request->getParsedBody();
		$response = $this->model->deleteLibraryItem($body['id']);

		if ((bool)$response === TRUE)
		{
			$this->set_flash_message("Successfully deleted anime.", 'success');
			// $this->cache->purge();
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
		$data = $this->model->getAnime($anime_id);

		$this->outputHTML('anime/details', [
			'title' => 'Anime &middot ' . $data['titles'][0],
			'data' => $data,
		]);
	}

}
// End of AnimeController.php