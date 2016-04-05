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
use Aviat\AnimeClient\Config;
use Aviat\AnimeClient\UrlGenerator;
use Aviat\AnimeClient\Model\Anime as AnimeModel;
use Aviat\AnimeClient\Model\AnimeCollection as AnimeCollectionModel;

/**
 * Controller for Anime collection pages
 */
class Collection extends BaseController {

	/**
	 * The anime collection model
	 * @var AnimeCollectionModel $anime_collection_model
	 */
	private $anime_collection_model;

	/**
	 * The anime API model
	 * @var AnimeModel $anime_model
	 */
	private $anime_model;

	/**
	 * Data to ve sent to all routes in this controller
	 * @var array $base_data
	 */
	protected $base_data;

	/**
	 * Url Generator class
	 * @var UrlGenerator
	 */
	protected $urlGenerator;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->urlGenerator = $container->get('url-generator');
		$this->anime_model = $container->get('anime-model');
		$this->anime_collection_model = $container->get('anime-collection-model');
		$this->base_data = array_merge($this->base_data, [
			'menu_name' => 'collection',
			'url_type' => 'anime',
			'other_type' => 'manga',
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
		$queryParams = $this->request->getQueryParams();
		$query = $queryParams['query'];
		$this->outputJSON($this->anime_model->search($query));
	}

	/**
	 * Show the anime collection page
	 *
	 * @param string $view
	 * @return void
	 */
	public function index($view)
	{
		$view_map = [
			'' => 'cover',
			'list' => 'list'
		];

		$data = $this->anime_collection_model->get_collection();

		$this->outputHTML('collection/' . $view_map[$view], [
			'title' => $this->config->get('whose_list') . "'s Anime Collection",
			'sections' => $data,
			'genres' => $this->anime_collection_model->get_genre_list()
		]);
	}

	/**
	 * Show the anime collection add/edit form
	 *
	 * @param integer|null $id
	 * @return void
	 */
	public function form($id = NULL)
	{
		$this->set_session_redirect();

		$action = (is_null($id)) ? "Add" : "Edit";

		$this->outputHTML('collection/' . strtolower($action), [
			'action' => $action,
			'action_url' => $this->urlGenerator->full_url('collection/' . strtolower($action)),
			'title' => $this->config->get('whose_list') . " Anime Collection &middot; {$action}",
			'media_items' => $this->anime_collection_model->get_media_type_list(),
			'item' => ($action === "Edit") ? $this->anime_collection_model->get($id) : []
		]);
	}

	/**
	 * Update a collection item
	 *
	 * @return void
	 */
	public function edit()
	{
		$data = $this->request->getParsedBody();
		if (array_key_exists('hummingbird_id', $data))
		{
			$this->anime_collection_model->update($data);
			$this->set_flash_message('Successfully updated collection item.', 'success');
		}
		else
		{
			$this->set_flash_message('Failed to update collection item', 'error');
		}

		$this->session_redirect();
	}

	/**
	 * Add a collection item
	 *
	 * @return void
	 */
	public function add()
	{
		$data = $this->request->getParsedBody();
		if (array_key_exists('id', $data))
		{
			$this->anime_collection_model->add($data);
			$this->set_flash_message('Successfully added collection item', 'success');
		}
		else
		{
			$this->set_flash_message('Failed to add collection item.', 'error');
		}

		$this->session_redirect();
	}

	/**
	 * Remove a collection item
	 *
	 * @return void
	 */
	public function delete()
	{
		$data = $this->request->getParsedBody();
		if ( ! array_key_exists('id', $data))
		{
			$this->redirect("collection/view", 303);
		}

		$this->anime_collection_model->delete($data);

		$this->redirect("collection/view", 303);
	}
}
// End of CollectionController.php