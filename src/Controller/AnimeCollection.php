<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.1
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
	Anime as AnimeModel,
	AnimeCollection as AnimeCollectionModel
};
use Aviat\Ion\Di\ContainerInterface;

/**
 * Controller for Anime collection pages
 */
final class AnimeCollection extends BaseController {

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
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->animeModel = $container->get('anime-model');
		$this->animeCollectionModel = $container->get('anime-collection-model');
		$this->baseData = array_merge($this->baseData, [
			'collection_type' => 'anime',
			'menu_name' => 'collection',
			'other_type' => 'manga',
			'url_type' => 'anime',
		]);
	}

	/**
	 * Search for anime
	 *
	 * @throws \Aviat\Ion\Exception\DoubleRenderException
	 * @return void
	 */
	public function search(): void
	{
		$queryParams = $this->request->getQueryParams();
		$query = $queryParams['query'];
		$this->outputJSON($this->animeModel->search($query));
	}

	/**
	 * Show the anime collection page
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
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \Aura\Router\Exception\RouteNotFound
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function form($id = NULL): void
	{
		$this->checkAuth();

		$this->setSessionRedirect();

		$action = $id === NULL ? 'Add' : 'Edit';
		$urlAction = strtolower($action);

		$this->outputHTML('collection/' . $urlAction, [
			'action' => $action,
			'action_url' => $this->url->generate("anime.collection.{$urlAction}.post"),
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Anime Collection",
				$action
			),
			'media_items' => $this->animeCollectionModel->getMediaTypeList(),
			'item' => ($action === 'Edit') ? $this->animeCollectionModel->get($id) : []
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
		$this->checkAuth();

		$data = $this->request->getParsedBody();
		if (array_key_exists('hummingbird_id', $data))
		{
			// @TODO verify data was updated correctly
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
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function add(): void
	{
		$this->checkAuth();

		$data = $this->request->getParsedBody();
		if (array_key_exists('id', $data))
		{
			// Check for existing entry
			if ($this->animeCollectionModel->get($data['id']) !== FALSE)
			{
				$this->setFlashMessage('Anime already exists, can not create duplicate', 'info');
			}
			else
			{
				// @TODO actually verify that collection item was added
				$this->animeCollectionModel->add($data);
				$this->setFlashMessage('Successfully added collection item', 'success');
			}
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
		$this->checkAuth();

		$data = $this->request->getParsedBody();
		if ( ! array_key_exists('hummingbird_id', $data))
		{
			$this->redirect('/anime-collection/view', 303);
		}

		// @TODO verify that item was actually deleted
		$this->animeCollectionModel->delete($data);
		$this->setFlashMessage('Successfully removed anime from collection.', 'success');

		$this->redirect('/anime-collection/view', 303);
	}
}
// End of AnimeCollection.php