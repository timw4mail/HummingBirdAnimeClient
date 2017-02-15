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
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\MAL;

use Amp\Artax\Request;
use Aviat\AnimeClient\API\MAL as M;
use Aviat\AnimeClient\API\MAL\ListItem;
use Aviat\AnimeClient\API\MAL\Transformer\AnimeListTransformer;
use Aviat\AnimeClient\API\XML;
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
	 * MAL Model constructor.
	 */
	public function __construct(ListItem $listItem)
	{
		$this->animeListTransformer = new AnimeListTransformer();
		$this->listItem = $listItem;
	}
	
	public function createFullListItem(array $data): Request
	{
		return $this->listItem->create($data);
	}

	public function createListItem(array $data): Request
	{
		$createData = [
			'id' => $data['id'],
			'data' => [
				'status' => M::KITSU_MAL_WATCHING_STATUS_MAP[$data['status']]
			]
		];

		return $this->listItem->create($createData);
	}

	public function getFullList(): array
	{
		$config = $this->container->get('config');
		$userName = $config->get(['mal', 'username']);
		$list = $this->getRequest('https://myanimelist.net/malappinfo.php', [
			'headers' => [
				'Accept' => 'text/xml'
			],
			'query' => [
				'u' => $userName,
				'status' => 'all'
			]
		]);

		return $list['myanimelist']['anime'];
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
}