<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
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

		return Json::decode($response->getBody()->buffer());
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
	 */
	public function createListItem(array $data, string $type = 'anime'): ?Request
	{
		$mediaId = $this->getMediaId($data, $type);
		if (empty($mediaId))
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
	 * Increase the watch count for the current list item
	 *
	 * @param string $type - Them media type (anime/manga)
	 */
	public function incrementListItem(FormItem $data, string $type): ?Request
	{
		$id = $this->getListIdFromData($data, $type);
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
		$id = $this->getListIdFromData($data, $type);
		if ($id === NULL)
		{
			return NULL;
		}

		return $this->listItem->update($id, $data['data']);
	}

	/**
	 * Remove a list item
	 *
	 * @param FormItem $data - The entry to remove
	 * @param string $type - The media type (anime/manga)
	 */
	public function deleteItem(FormItem $data, string $type): ?Request
	{
		$mediaId = $this->getMediaId((array) $data, $type);
		if ($mediaId === NULL)
		{
			return NULL;
		}

		$id = $this->getListIdFromMediaId($mediaId);
		if (is_string($id))
		{
			return $this->listItem->delete($id);
		}

		return NULL;
	}

	/**
	 * Get the id of the specific list entry from the data
	 */
	public function getListIdFromData(FormItem $data, string $type = 'ANIME'): ?string
	{
		$mediaId = $this->getMediaId((array) $data, $type);
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
	 */
	private function getMediaId(array $data, string $type = 'ANIME'): ?string
	{
		if (isset($data['anilist_id']))
		{
			return $data['anilist_id'];
		}

		return (isset($data['mal_id']))
			? $this->getMediaIdFromMalId($data['mal_id'], mb_strtoupper($type))
			: NULL;
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
