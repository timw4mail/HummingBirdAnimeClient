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
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Anilist;

use function Amp\Promise\wait;

use InvalidArgumentException;

use Amp\Artax\Request;
use Aviat\AnimeClient\API\Anilist;
use Aviat\AnimeClient\API\Mapping\{AnimeWatchingStatus, MangaReadingStatus};
use Aviat\AnimeClient\Types\FormItem;
use Aviat\Ion\Json;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;

use Throwable;

/**
 * Anilist API Model
 */
final class Model
{
	use AnilistTrait;
	/**
	 * @var ListItem
	 */
	private $listItem;

	/**
	 * Constructor
	 *
	 * @param ListItem $listItem
	 */
	public function __construct(ListItem $listItem)
	{
		$this->listItem = $listItem;
	}

	// -------------------------------------------------------------------------
	// ! Generic API calls
	// -------------------------------------------------------------------------

	/**
	 * Attempt to get an auth token
	 *
	 * @param string $code - The request token
	 * @param string $redirectUri - The oauth callback url
	 * @return array
	 * @throws Throwable
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

		$response = $this->getResponseFromRequest($request);

		return Json::decode(wait($response->getBody()));
	}

	/**
	 * Check auth status with simple API call
	 *
	 * @return array
	 */
	public function checkAuth(): array
	{
		return $this->runQuery('CheckLogin');
	}

	/**
	 * Get user list data for syncing with Kitsu
	 *
	 * @param string $type
	 * @return array
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

		return $this->runQuery('SyncUserList', [
			'name' => $anilistUser,
			'type' => $type,
		]);
	}

	/**
	 * Create a list item
	 *
	 * @param array $data
	 * @param string $type
	 * @return Request
	 */
	public function createListItem(array $data, string $type = 'anime'): ?Request
	{
		$createData = [];

		$mediaId = $this->getMediaIdFromMalId($data['mal_id'], mb_strtoupper($type));

		if ($mediaId === NULL)
		{
			return NULL;
		}

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
	 *
	 * @param array $data
	 * @param string $type
	 * @return Request
	 */
	public function createFullListItem(array $data, string $type): Request
	{
		$createData = $data['data'];
		$mediaId = $this->getMediaIdFromMalId($data['mal_id'], strtoupper($type));

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
	 * @return array
	 */
	public function getListItem(string $malId, string $type): array
	{
		$id = $this->getListIdFromMalId($malId, $type);

		$data = $this->listItem->get($id)['data'];

		return ($data !== null)
			? $data['MediaList']
			: [];
	}

	/**
	 * Increase the watch count for the current list item
	 *
	 * @param FormItem $data
	 * @param string $type - Them media type (anime/manga)
	 * @return Request
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
	 * @param FormItem $data
	 * @param string $type - Them media type (anime/manga)
	 * @return Request
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
	 * @return Request
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

	/**
	 * Get the id of the specific list entry from the malId
	 *
	 * @param string $malId
	 * @param string $type - The media type (anime/manga)
	 * @return string
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
	private function getListIdFromMediaId(string $mediaId): string
	{
		$config = $this->container->get('config');
		$anilistUser = $config->get(['anilist', 'username']);

		$info = $this->runQuery('ListItemIdByMediaId', [
			'id' => $mediaId,
			'userName' => $anilistUser,
		]);

		return (string)$info['data']['MediaList']['id'];
	}

	/**
	 * Get the Anilist media id from the malId
	 *
	 * @param string $malId
	 * @param string $type
	 * @return string
	 */
	private function getMediaIdFromMalId(string $malId, string $type = 'ANIME'): ?string
	{
		if ($malId === '')
		{
			return NULL;
		}

		$info = $this->runQuery('MediaIdByMalId', [
			'id' => $malId,
			'type' => mb_strtoupper($type),
		]);

		if (array_key_exists('errors', $info))
		{
			return NULL;
		}

		return (string)$info['data']['Media']['id'];
	}
}