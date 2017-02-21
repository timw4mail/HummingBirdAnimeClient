<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\Model\{
	Anime as AnimeModel,
	AnimeCollection as AnimeCollectionModel
};
use Aviat\AnimeClient\UrlGenerator;
use Aviat\Ion\Di\ContainerInterface;

/**
 * Controller for Anime collection pages
 */
class Collection extends BaseController {

	/**
	 * The anime collection model
	 * @var AnimeCollectionModel $animeCollectionModel
	 */
	private $animeCollectionModel;

	/**
	 * The anime API model
	 * @var AnimeModel $animeModel
	 */
	private $animeModel;

	/**
	 * Data to be sent to all routes in this controller
	 * @var array $baseData
	 */
	protected $baseData;

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
		$this->animeModel = $container->get('anime-model');
		$this->animeCollectionModel = $container->get('anime-collection-model');
		$this->baseData = array_merge($this->baseData, [
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
		$this->outputJSON($this->animeModel->search($query));
	}

	/**
	 * Show the anime collection page
	 *
	 * @param string $view
	 * @return void
	 */
	public function index($view)
	{
		$viewMap = [
			'' => 'cover',
			'list' => 'list'
		];

		$data = $this->animeCollectionModel->getCollection();

		$this->outputHTML('collection/' . $viewMap[$view], [
			'title' => $this->config->get('whose_list') . "'s Anime Collection",
			'sections' => $data,
			'genres' => $this->animeCollectionModel->getGenreList()
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
		$this->setSessionRedirect();

		$action = (is_null($id)) ? "Add" : "Edit";

		$this->outputHTML('collection/' . strtolower($action), [
			'action' => $action,
			'action_url' => $this->urlGenerator->fullUrl('collection/' . strtolower($action)),
			'title' => $this->config->get('whose_list') . " Anime Collection &middot; {$action}",
			'media_items' => $this->animeCollectionModel->getMediaTypeList(),
			'item' => ($action === "Edit") ? $this->animeCollectionModel->get($id) : []
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
			$this->animeCollectionModel->update($data);
			$this->setFlashMessage('Successfully updated collection item.', 'success');
		}
		else
		{
			$this->setFlashMessage('Failed to update collection item', 'error');
		}

		$this->sessionRedirect();
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
			$this->animeCollectionModel->add($data);
			$this->setFlashMessage('Successfully added collection item', 'success');
		}
		else
		{
			$this->setFlashMessage('Failed to add collection item.', 'error');
		}

		$this->sessionRedirect();
	}

	/**
	 * Remove a collection item
	 *
	 * @return void
	 */
	public function delete()
	{
		$data = $this->request->getParsedBody();
		if ( ! array_key_exists('hummingbird_id', $data))
		{
			$this->redirect("/collection/view", 303);
		}

		$this->animeCollectionModel->delete($data);
		$this->setFlashMessage("Successfully removed anime from collection.", 'success');

		$this->redirect("/collection/view", 303);
	}
}
// End of CollectionController.php