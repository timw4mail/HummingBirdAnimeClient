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
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\Model\{
	manga as mangaModel,
	mangaCollection as mangaCollectionModel
};
use Aviat\AnimeClient\UrlGenerator;
use Aviat\Ion\Di\ContainerInterface;

/**
 * Controller for manga collection pages
 */
class MangaCollection extends BaseController {

	/**
	 * The manga collection model
	 * @var mangaCollectionModel $mangaCollectionModel
	 */
	private $mangaCollectionModel;

	/**
	 * The manga API model
	 * @var mangaModel $mangaModel
	 */
	private $mangaModel;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->mangaModel = $container->get('manga-model');
		$this->mangaCollectionModel = $container->get('manga-collection-model');
		$this->baseData = array_merge($this->baseData, [
			'collection_type' => 'manga',
			'menu_name' => 'manga-collection',
			'url_type' => 'manga',
			'other_type' => 'anime',
			'config' => $this->config,
		]);
	}

	/**
	 * Search for manga
	 *
	 * @return void
	 */
	public function search()
	{
		$queryParams = $this->request->getQueryParams();
		$query = $queryParams['query'];
		$this->outputJSON($this->mangaModel->search($query));
	}

	/**
	 * Show the manga collection page
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

		$data = $this->mangaCollectionModel->getCollection();

		$this->outputHTML('collection/' . $viewMap[$view], [
			'title' => $this->config->get('whose_list') . "'s Manga Collection",
			'sections' => $data,
			'genres' => $this->mangaCollectionModel->getGenreList()
		]);
	}

	/**
	 * Show the manga collection add/edit form
	 *
	 * @param integer|null $id
	 * @return void
	 */
	public function form($id = NULL)
	{
		$this->setSessionRedirect();

		$action = (is_null($id)) ? "Add" : "Edit";
		$urlAction = strtolower($action);

		$this->outputHTML('collection/' . $urlAction, [
			'action' => $action,
			'action_url' => $this->url->generate("manga.collection.{$urlAction}.post"),
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s manga Collection",
				$action
			),
			'media_items' => $this->mangaCollectionModel->getMediaTypeList(),
			'item' => ($action === "Edit") ? $this->mangaCollectionModel->get($id) : []
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
			$this->mangaCollectionModel->update($data);
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
			$this->mangaCollectionModel->add($data);
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
			$this->redirect("/manga-collection/view", 303);
		}

		$this->mangaCollectionModel->delete($data);
		$this->setFlashMessage("Successfully removed manga from collection.", 'success');

		$this->redirect("/manga-collection/view", 303);
	}
}
// End of CollectionController.php