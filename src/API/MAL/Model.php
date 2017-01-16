<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\MAL;

use Aviat\AnimeClient\API\MAL as M;
use Aviat\AnimeClient\API\MAL\{
	AnimeListTransformer,
	ListItem
};
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
	 * KitsuModel constructor.
	 */
	public function __construct(ListItem $listItem)
	{
		// Set up Guzzle trait
		$this->init();
		$this->animeListTransformer = new AnimeListTransformer();
		$this->listItem = $listItem;
	}

	public function createListItem(array $data): bool
	{
		return FALSE;
	}

	public function getListItem(string $listId): array
	{
		return [];
	}

	public function updateListItem(array $data)
	{
		$updateData = $this->animeListTransformer->transform($data['data']);
		return $this->listItem->update($data['mal_id'], $updateData);
	}

	public function deleteListItem(string $id): bool
	{

	}
}