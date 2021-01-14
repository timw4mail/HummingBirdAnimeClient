<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aura\Router\Exception\RouteNotFound;
use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\Model\{
	Anime as AnimeModel,
	AnimeCollection as AnimeCollectionModel
};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;
use Aviat\Ion\Exception\DoubleRenderException;

use InvalidArgumentException;

/**
 * Controller for Anime collection pages
 */
final class AnimeCollection extends BaseController {

	/**
	 * The anime collection model
	 * @var AnimeCollectionModel $animeCollectionModel
	 */
	private AnimeCollectionModel $animeCollectionModel;

	/**
	 * The anime API model
	 * @var AnimeModel $animeModel
	 */
	private AnimeModel $animeModel;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerException
	 * @throws NotFoundException
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

	public function index(): void
	{
		$this->redirect('/anime-collection/view', 303);
	}

	/**
	 * Search for anime
	 *
	 * @throws DoubleRenderException
	 * @return void
	 */
	public function search(): void
	{
		$queryParams = $this->request->getQueryParams();
		$query = $queryParams['query'];
		$this->outputJSON($this->animeModel->search($query), 200);
	}

	/**
	 * Show the anime collection page
	 *
	 * @param string|null $view
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function view(?string $view = ''): void
	{
		$viewMap = [
			'' => 'cover',
			'list' => 'list'
		];

		$sections = array_merge(
			['All' => $this->animeCollectionModel->getFlatCollection()],
			$this->animeCollectionModel->getCollection()
		);

		$this->outputHTML('collection/' . $viewMap[$view], [
			'title' => $this->config->get('whose_list') . "'s Anime Collection",
			'sections' => $sections,
		]);
	}

	/**
	 * Show the anime collection add/edit form
	 *
	 * @param integer|null $id
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws RouteNotFound
	 * @throws InvalidArgumentException
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
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function edit(): void
	{
		$this->checkAuth();
		$this->update($this->request->getParsedBody());
	}

	/**
	 * Add a collection item
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws InvalidArgumentException
	 * @return void
	 */
	public function add(): void
	{
		$this->checkAuth();

		$data = $this->request->getParsedBody();
		if (array_key_exists('id', $data))
		{
			// Check for existing entry
			if ($this->animeCollectionModel->has($data['id']))
			{
				// Let's just update with the data we have
				// if the entry already exists.
				$data['hummingbird_id'] = $data['id'];
				unset(
					$data['id'],
					$data['mal_id'],
					$data['search']
				);

				// Don't overwrite notes if the box is empty
				if (trim($data['notes']) === '')
				{
					unset($data['notes']);
				}

				$this->update($data);
				return;
			}

			$this->animeCollectionModel->add($data);

			// Verify the item was added
			if ($this->animeCollectionModel->wasAdded($data))
			{
				$this->setFlashMessage('Successfully added collection item', 'success');
				$this->sessionRedirect();
				return;
			}
		}

		$this->setFlashMessage('Failed to add collection item.', 'error');
		$this->redirect('/anime-collection/add', 303);
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
			$this->setFlashMessage("Can't delete item that doesn't exist", 'error');
			$this->redirect('/anime-collection/view', 303);
		}

		$this->animeCollectionModel->delete($data);

		// Verify that item was actually deleted
		($this->animeCollectionModel->wasDeleted($data))
			? $this->setFlashMessage('Successfully removed anime from collection.', 'success')
			: $this->setFlashMessage('Failed to delete item from collection.', 'error');

		$this->redirect('/anime-collection/view', 303);
	}

	/**
	 * Update a collection item
	 *
	 * @param $data
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	protected function update($data): void
	{
		if (array_key_exists('hummingbird_id', $data))
		{
			$this->animeCollectionModel->update($data);

			// Verify the item was actually updated
			($this->animeCollectionModel->wasUpdated($data))
				? $this->setFlashMessage('Successfully updated collection item.', 'success')
				: $this->setFlashMessage('Failed to update collection item.', 'error');
		}
		else
		{
			$this->setFlashMessage('No item id to update. Update failed.', 'error');
		}

		$this->sessionRedirect();
	}
}
// End of AnimeCollection.php