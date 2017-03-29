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

namespace Aviat\AnimeClient\API\MAL;

use Amp\Artax\Request;
use Aviat\AnimeClient\API\MAL\{ListItem, Transformer\AnimeListTransformer};
use Aviat\AnimeClient\API\XML;
use Aviat\AnimeClient\API\Mapping\{AnimeWatchingStatus, MangaReadingStatus};
use Aviat\Ion\Di\ContainerAware;

/**
 * MyAnimeList API Model
 */
class Model {
	use ContainerAware;
	use MALTrait;

	/**
	 * @var AnimeListTransformer
	 */
	protected $animeListTransformer;
	
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

	public function createListItem(array $data, string $type = 'anime'): Request
	{
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



		return $this->listItem->create($createData);
	}

	public function getMangaList(): array
	{
		return $this->getList('manga');
	}

	public function getAnimeList(): array
	{
		return $this->getList('anime');
	}

	public function getListItem(string $listId): array
	{
		return [];
	}

	public function updateListItem(array $data): Request
	{
		$updateData = $this->animeListTransformer->untransform($data);
		return $this->listItem->update($updateData['id'], $updateData['data']);
	}

	public function deleteListItem(string $id): Request
	{
		return $this->listItem->delete($id);
	}

	private function getList(string $type): array
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

		return $list['myanimelist'][$type];
	}
}