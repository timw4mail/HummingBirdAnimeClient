<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\Model\{
	Manga as MangaModel,
	MangaCollection as MangaCollectionModel
};
use Aviat\Ion\Di\ContainerInterface;

/**
 * Controller for manga collection pages
 */
final class MangaCollection extends BaseController {

	/**
	 * The manga collection model
	 * @var MangaCollectionModel $mangaCollectionModel
	 */
	private $mangaCollectionModel;

	/**
	 * The manga API model
	 * @var MangaModel $mangaModel
	 */
	private $mangaModel;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->mangaModel = $container->get('manga-model');
		$this->mangaCollectionModel = $container->get('manga-collection-model');
		$this->baseData = array_merge($this->baseData, [
			'collection_type' => 'manga',
			'menu_name' => 'manga-collection',
			'other_type' => 'anime',
			'url_type' => 'manga',
		]);
	}

	/**
	 * Search for manga
	 *
	 * @throws \Aviat\Ion\Exception\DoubleRenderException
	 * @return void
	 */
	public function search(): void
	{
		$queryParams = $this->request->getQueryParams();
		$query = $queryParams['query'];
		$this->outputJSON($this->mangaModel->search($query));
	}

	/**
	 * Show the manga collection page
	 *
	 * @param string $view
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function index($view): void
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
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \Aura\Router\Exception\RouteNotFound
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function form($id = NULL): void
	{
		$this->setSessionRedirect();

		$action = $id === NULL ? 'Add' : 'Edit';
		$urlAction = strtolower($action);

		$this->outputHTML('collection/' . $urlAction, [
			'action' => $action,
			'action_url' => $this->url->generate("manga.collection.{$urlAction}.post"),
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s manga Collection",
				$action
			),
			'media_items' => $this->mangaCollectionModel->getMediaTypeList(),
			'item' => ($action === 'Edit') ? $this->mangaCollectionModel->get($id) : []
		]);
	}

	/**
	 * Update a collection item
	 *
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function edit(): void
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
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function add(): void
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
	public function delete(): void
	{
		$data = $this->request->getParsedBody();
		if ( ! array_key_exists('hummingbird_id', $data))
		{
			$this->redirect('/manga-collection/view', 303);
		}

		$this->mangaCollectionModel->delete($data);
		$this->setFlashMessage('Successfully removed manga from collection.', 'success');

		$this->redirect('/manga-collection/view', 303);
	}
}
// End of CollectionController.php