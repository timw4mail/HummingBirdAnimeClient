<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Anilist;

use Amp\Http\Client\Request;

use Aviat\AnimeClient\Anilist;

use Aviat\AnimeClient\API\Mapping\{AnimeWatchingStatus, MangaReadingStatus};
use Aviat\AnimeClient\Types\FormItem;
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use Aviat\Ion\Json;
use InvalidArgumentException;
use Throwable;
use function Amp\Promise\wait;

/**
 * Anilist API Model
 */
final class Model
{
	use RequestBuilderTrait;

	/**
	 * Constructor
	 */
	public function __construct(private ListItem $listItem)
	{
	}

	// -------------------------------------------------------------------------
	// ! Generic API calls
	// -------------------------------------------------------------------------
	/**
	 * Attempt to get an auth token
	 *
	 * @param string $code - The request token
	 * @param string $redirectUri - The oauth callback url
	 * @throws Throwable
	 * @return mixed[]
	 */
	public function authenticate(string $code, string $redirectUri): array
	{
		$config = $this->getContainer()->get('config');
		$request = $this->requestBuilder
			->newRequest('POST', Anilist::TOKEN_URL)
			->setJsonBody([
				'grant_type' => 'authorization_code',
				'client_id' => $config->get(['anilist', 'client_id']),
				'client_secret' => $config->get(['anilist', 'client_secret']),
				'redirect_uri' => $redirectUri,
				'code' => $code,
			])
			->getFullRequest();

		$response = $this->requestBuilder->getResponseFromRequest($request);

		return Json::decode(wait($response->getBody()->buffer()));
	}

	/**
	 * Check auth status with simple API call
	 */
	public function checkAuth(): array
	{
		return $this->requestBuilder->runQuery('CheckLogin');
	}

	/**
	 * Get user list data for syncing with Kitsu
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function getSyncList(string $type = 'anime'): array
	{
		$config = $this->container->get('config');
		$anilistUser = $config->get(['anilist', 'username']);

		if ( ! (is_string($anilistUser) && $anilistUser !== ''))
		{
			throw new InvalidArgumentException('Anilist username is not defined in config');
		}

		return $this->requestBuilder->runQuery('SyncUserList', [
			'name' => $anilistUser,
			'type' => $type,
		]);
	}

	/**
	 * Create a list item
	 *
	 * @return Request
	 */
	public function createListItem(array $data, string $type = 'anime'): ?Request
	{
		$mediaId = $this->getMediaId($data, $type);
		if ($mediaId === NULL)
		{
			return NULL;
		}

		$createData = [];

		if ($type === 'ANIME')
		{
			$createData = [
				'id' => $mediaId,
				'status' => AnimeWatchingStatus::KITSU_TO_ANILIST[$data['status']],
			];
		}
		elseif ($type === 'MANGA')
		{
			$createData = [
				'id' => $mediaId,
				'status' => MangaReadingStatus::KITSU_TO_ANILIST[$data['status']],
			];
		}

		return $this->listItem->create($createData);
	}

	/**
	 * Create a list item with all the relevant data
	 */
	public function createFullListItem(array $data, string $type): Request
	{
		$createData = $data['data'];
		$mediaId = $this->getMediaId($data, $type);

		if (empty($mediaId))
		{
			throw new MissingIdException('No id mapping found');
		}

		$createData['id'] = $mediaId;

		return $this->listItem->createFull($createData);
	}

	/**
	 * Get the data for a specific list item, generally for editing
	 *
	 * @param string $malId - The unique identifier of that list item
	 * @param string $type - Them media type (anime/manga)
	 *
	 * @return mixed[]
	 */
	public function getListItem(string $malId, string $type): array
	{
		$id = $this->getListIdFromMalId($malId, $type);
		if ($id === NULL)
		{
			return [];
		}

		$data = $this->listItem->get($id)['data'];

		return ($data !== NULL)
			? $data['MediaList']
			: [];
	}

	/**
	 * Increase the watch count for the current list item
	 *
	 * @param string $type - Them media type (anime/manga)
	 */
	public function incrementListItem(FormItem $data, string $type): ?Request
	{
		$id = $this->getListIdFromMalId($data['mal_id'], $type);
		if ($id === NULL)
		{
			return NULL;
		}

		return $this->listItem->increment($id, $data['data']);
	}

	/**
	 * Modify a list item
	 *
	 * @param string $type - Them media type (anime/manga)
	 */
	public function updateListItem(FormItem $data, string $type): ?Request
	{
		$id = $this->getListIdFromMalId($data['mal_id'], mb_strtoupper($type));

		if ($id === NULL)
		{
			return NULL;
		}

		return $this->listItem->update($id, $data['data']);
	}

	/**
	 * Remove a list item
	 *
	 * @param string $malId - The id of the list item to remove
	 * @param string $type - Them media type (anime/manga)
	 */
	public function deleteListItem(string $malId, string $type): ?Request
	{
		$id = $this->getListIdFromMalId($malId, $type);
		if ($id === NULL)
		{
			return NULL;
		}

		return $this->listItem->delete($id);
	}

	public function deleteItem(FormItem $data, string $type): ?Request
	{
		$mediaId = $this->getMediaId((array)$data, $type);

		return $this->listItem->delete($mediaId);
	}

	/**
	 * Get the id of the specific list entry from the malId
	 *
	 * @param string $type - The media type (anime/manga)
	 */
	public function getListIdFromMalId(string $malId, string $type): ?string
	{
		$mediaId = $this->getMediaIdFromMalId($malId, $type);
		if ($mediaId === NULL)
		{
			return NULL;
		}

		return $this->getListIdFromMediaId($mediaId);
	}

	/**
	 * Get the Anilist list item id from the media id from its MAL id
	 * this way is more accurate than getting the list item id
	 * directly from the MAL id
	 */
	private function getListIdFromMediaId(string $mediaId): ?string
	{
		$config = $this->container->get('config');
		$anilistUser = $config->get(['anilist', 'username']);

		$info = $this->requestBuilder->runQuery('ListItemIdByMediaId', [
			'id' => $mediaId,
			'userName' => $anilistUser,
		]);

		if ( ! empty($info['errors']))
		{
			return NULL;
		}

		return (string) $info['data']['MediaList']['id'];
	}

	/**
	 * Find the id to update by
	 *
	 * @param array $data
	 * @param string $type
	 * @return string|null
	 */
	private function getMediaId (array $data, string $type = 'ANIME'): ?string
	{
		if ($data['anilist_id'] !== NULL)
		{
			return $data['anilist_id'];
		}

		if ($data['mal_id'] !== NULL)
		{
			return $this->getMediaIdFromMalId($data['mal_id'], mb_strtoupper($type));
		}

		return NULL;
	}

	/**
	 * Get the Anilist media id from the malId
	 */
	private function getMediaIdFromMalId(string $malId, string $type = 'ANIME'): ?string
	{
		if ($malId === '')
		{
			return NULL;
		}

		$info = $this->requestBuilder->runQuery('MediaIdByMalId', [
			'id' => $malId,
			'type' => mb_strtoupper($type),
		]);

		if (array_key_exists('errors', $info))
		{
			return NULL;
		}

		return (string) $info['data']['Media']['id'];
	}
}
