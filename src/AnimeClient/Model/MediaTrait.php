<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\API\Anilist;
use Aviat\AnimeClient\API\Kitsu;
use Aviat\AnimeClient\API\ParallelAPIRequest;
use Aviat\AnimeClient\Types\AnimeListItem;
use Aviat\AnimeClient\Types\FormItem;
use Aviat\AnimeClient\Types\MangaListItem;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;

use Throwable;

/**
 * Common functionality for Anime/Manga Models
 */
trait MediaTrait {

	/**
	 * Is the Anilist API enabled?
	 *
	 * @var boolean
	 */
	protected bool $anilistEnabled;

	/**
	 * Model for making requests to Anilist API
	 *
	 * @var Anilist\Model
	 */
	protected Anilist\Model $anilistModel;

	/**
	 * Model for making requests to Kitsu API
	 *
	 * @var Kitsu\Model
	 */
	protected Kitsu\Model $kitsuModel;

	/**
	 * Anime constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->anilistModel = $container->get('anilist-model');
		$this->kitsuModel = $container->get('kitsu-model');

		$config = $container->get('config');
		$this->anilistEnabled = (bool) $config->get(['anilist', 'enabled']);
	}

	/**
	 * Search for anime by name
	 *
	 * @param string $name
	 * @return array
	 */
	public function search(string $name): array
	{
		return $this->kitsuModel->search($this->type, urldecode($name));
	}

	/**
	 * Get information about a specific list item
	 * for editing/updating that item
	 *
	 * @param string $itemId
	 * @return AnimeListItem|MangaListItem
	 */
	public function getLibraryItem(string $itemId)
	{
		return $this->kitsuModel->getListItem($itemId);
	}

	/**
	 * Add an anime to your list
	 *
	 * @param array $data
	 * @return bool
	 * @throws Throwable
	 */
	public function createLibraryItem(array $data): bool
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->createListItem($data), 'kitsu');

		if ($this->anilistEnabled && $data['mal_id'] !== null)
		{
			// If can't map MAL id, this will be null
			$maybeRequest = $this->anilistModel->createListItem($data, strtoupper($this->type));
			if ($maybeRequest !== NULL)
			{
				$requester->addRequest($maybeRequest, 'anilist');
			}
		}

		$results = $requester->makeRequests();

		return count($results) > 0;
	}

	/**
	 * Increment progress for the specified anime
	 *
	 * @param FormItem $data
	 * @return array
	 * @throws Throwable
	 */
	public function incrementLibraryItem(FormItem $data): array
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->incrementListItem($data), 'kitsu');

		if (( ! empty($data['mal_id'])) && $this->anilistEnabled)
		{
			// If can't map MAL id, this will be null
			$maybeRequest = $this->anilistModel->incrementListItem($data, strtoupper($this->type));
			if ($maybeRequest !== NULL)
			{
				$requester->addRequest($maybeRequest, 'anilist');
			}
		}

		$results = $requester->makeRequests();

		$body = Json::decode($results['kitsu']);
		$statusCode = array_key_exists('error', $body) ? 400 : 200;

		return [
			'body' => Json::decode($results['kitsu']),
			'statusCode' => $statusCode
		];
	}

	/**
	 * Update a list entry
	 *
	 * @param FormItem $data
	 * @return array
	 * @throws Throwable
	 */
	public function updateLibraryItem(FormItem $data): array
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->updateListItem($data), 'kitsu');

		if (( ! empty($data['mal_id'])) && $this->anilistEnabled)
		{
			// If can't map MAL id, this will be null
			$maybeRequest = $this->anilistModel->updateListItem($data, strtoupper($this->type));
			if ($maybeRequest !== NULL)
			{
				$requester->addRequest($maybeRequest, 'anilist');
			}
		}

		$results = $requester->makeRequests();

		$body = Json::decode($results['kitsu']);
		$statusCode = array_key_exists('errors', $body) ? 400: 200;

		return [
			'body' => Json::decode($results['kitsu']),
			'statusCode' => $statusCode
		];
	}

	/**
	 * Delete a list entry
	 *
	 * @param string $id
	 * @param string|null $malId
	 * @return bool
	 * @throws Throwable
	 */
	public function deleteLibraryItem(string $id, string $malId = NULL): bool
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->deleteListItem($id), 'kitsu');

		if ($this->anilistEnabled && $malId !== null)
		{
			// If can't map MAL id, this will be null
			$maybeRequest = $this->anilistModel->deleteListItem($malId, strtoupper($this->type));
			if ($maybeRequest !== NULL)
			{
				$requester->addRequest($maybeRequest, 'anilist');
			}
		}

		$results = $requester->makeRequests();

		return count($results) > 0;
	}
}