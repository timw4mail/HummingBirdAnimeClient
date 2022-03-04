<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\API\{Anilist, Kitsu, ParallelAPIRequest};
use Aviat\AnimeClient\Types\{AnimeListItem, FormItem, MangaListItem};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;

use Throwable;

/**
 * Common functionality for Anime/Manga Models
 */
trait MediaTrait
{
	/**
	 * Is the Anilist API enabled?
	 */
	protected bool $anilistEnabled;

	/**
	 * Model for making requests to Anilist API
	 */
	protected Anilist\Model $anilistModel;

	/**
	 * Model for making requests to Kitsu API
	 */
	protected Kitsu\Model $kitsuModel;

	/**
	 * Anime constructor.
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
	 * @return mixed[]
	 */
	public function search(string $name, bool $inCollection = FALSE): array
	{
		$data = $this->kitsuModel->search($this->type, urldecode($name));

		if ($inCollection)
		{
			// @TODO: allow filtering collection search by existing items
		}

		return $data;
	}

	/**
	 * Get information about a specific list item
	 * for editing/updating that item
	 */
	public function getLibraryItem(string $itemId): AnimeListItem|MangaListItem
	{
		return $this->kitsuModel->getListItem($itemId);
	}

	/**
	 * Add an anime to your list
	 *
	 * @throws Throwable
	 */
	public function createLibraryItem(array $data): bool
	{
		$requester = new ParallelAPIRequest();
		$kitsuRequest = $this->kitsuModel->createListItem($data);
		if ($kitsuRequest === NULL)
		{
			return FALSE;
		}

		$requester->addRequest($kitsuRequest, 'kitsu');

		if ($this->anilistEnabled && $data['mal_id'] !== NULL)
		{
			// If can't map MAL id, this will be null
			$maybeRequest = $this->anilistModel->createListItem($data, strtoupper($this->type));
			if ($maybeRequest !== NULL)
			{
				$requester->addRequest($maybeRequest, 'anilist');
			}
		}

		$results = $requester->makeRequests();

		return $results !== [];
	}

	/**
	 * Increment progress for the specified anime
	 *
	 * @throws Throwable
	 * @return array<string, mixed>
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
			'statusCode' => $statusCode,
		];
	}

	/**
	 * Update a list entry
	 *
	 * @throws Throwable
	 * @return array<string, mixed>
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
		$statusCode = array_key_exists('errors', $body) ? 400 : 200;

		return [
			'body' => Json::decode($results['kitsu']),
			'statusCode' => $statusCode,
		];
	}

	/**
	 * Delete a list entry
	 *
	 * @throws Throwable
	 */
	public function deleteLibraryItem(string $id, ?string $malId = NULL): bool
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->deleteListItem($id), 'kitsu');

		if ($this->anilistEnabled && $malId !== NULL)
		{
			// If can't map MAL id, this will be null
			$maybeRequest = $this->anilistModel->deleteListItem($malId, strtoupper($this->type));
			if ($maybeRequest !== NULL)
			{
				$requester->addRequest($maybeRequest, 'anilist');
			}
		}

		$results = $requester->makeRequests();

		return $results !== [];
	}
}
