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
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\MAL;

use Amp\Artax\Request;
use Aviat\AnimeClient\API\MAL\{
	ListItem,
	Transformer\AnimeListTransformer,
	Transformer\MangaListTransformer
};
use Aviat\AnimeClient\API\XML;
use Aviat\AnimeClient\API\Mapping\{AnimeWatchingStatus, MangaReadingStatus};
use Aviat\AnimeClient\Types\{Anime, FormItem};
use Aviat\Ion\Di\ContainerAware;

/**
 * MyAnimeList API Model
 */
final class Model {
	use ContainerAware;
	use MALTrait;

	/**
	 * @var AnimeListTransformer
	 */
	protected $animeListTransformer;

	/**
	 * @var MangaListTransformer
	 */
	protected $mangaListTransformer;

	/**
	 * @var ListItem
	 */
	protected $listItem;

	/**
	 * MAL Model constructor.
	 *
	 * @param ListItem $listItem
	 */
	public function __construct(ListItem $listItem)
	{
		$this->animeListTransformer = new AnimeListTransformer();
		$this->mangaListTransformer = new MangaListTransformer();
		$this->listItem = $listItem;
	}

	/**
	 * Create a list item on MAL
	 *
	 * @param array $data
	 * @param string $type "anime" or "manga"
	 * @return Request
	 */
	public function createFullListItem(array $data, string $type = 'anime'): Request
	{
		return $this->listItem->create($data, $type);
	}

	/**
	 * Create a list item on MAL from a Kitsu list item
	 *
	 * @param array $data
	 * @param string $type "anime" or "manga"
	 * @return Request
	 */
	public function createListItem(array $data, string $type = 'anime'): Request
	{
		$createData = [];

		if ($type === 'anime')
		{
			$createData = [
				'id' => $data['id'],
				'data' => [
					'status' => AnimeWatchingStatus::KITSU_TO_MAL[$data['status']]
				]
			];
		}
		elseif ($type === 'manga')
		{
			$createData = [
				'id' => $data['id'],
				'data' => [
					'status' => MangaReadingStatus::KITSU_TO_MAL[$data['status']]
				]
			];
		}

		return $this->listItem->create($createData, $type);
	}

	/**
	 * Get list info
	 *
	 * @param string $type "anime" or "manga"
	 * @return array
	 */
	public function getList(string $type = "anime"): array
	{
		$config = $this->container->get('config');
		$userName = $config->get(['mal', 'username']);
		$list = $this->getRequest('https://myanimelist.net/malappinfo.php', [
			'headers' => [
				'Accept' => 'text/xml'
			],
			'query' => [
				'u' => $userName,
				'status' => 'all',
				'type' => $type
			]
		]);

		return array_key_exists($type, $list['myanimelist'])
			? $list['myanimelist'][$type]
			: [];
	}

	/**
	 * Retrieve a list item
	 *
	 * Does not apply to MAL
	 *
	 * @param string $listId
	 * @return array
	 */
	public function getListItem(string $listId): array
	{
		return [];
	}

	/**
	 * Update a list item
	 *
	 * @param array $data
	 * @param string $type "anime" or "manga"
	 * @return Request
	 */
	public function updateListItem($data, string $type = 'anime'): Request
	{
		$updateData = [];

		if ($type === 'anime')
		{
			$updateData = $this->animeListTransformer->untransform($data);
		}
		else if ($type === 'manga')
		{
			$updateData = $this->mangaListTransformer->untransform($data);
		}

		return $this->listItem->update($updateData['id'], $updateData['data'], $type);
	}

	/**
	 * Delete a list item
	 *
	 * @param string $id
	 * @param string $type "anime" or "manga"
	 * @return Request
	 */
	public function deleteListItem(string $id, string $type = 'anime'): Request
	{
		return $this->listItem->delete($id, $type);
	}
}