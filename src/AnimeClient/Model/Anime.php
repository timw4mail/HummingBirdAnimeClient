<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\API\ParallelAPIRequest;
use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\Types\{
	Anime as AnimeType,
	FormItem,
	AnimeListItem
};
use Aviat\Ion\Json;

use Throwable;
use function is_array;

/**
 * Model for handling requests dealing with the anime list
 */
class Anime extends API {
	use MediaTrait;

	protected string $type = 'anime';

	/**
	 * Get a category out of the full list
	 *
	 * @param string $status
	 * @return array
	 */
	public function getList(string $status): array
	{
		$data = $this->kitsuModel->getAnimeList($status);
		$this->sortByName($data, 'anime');

		$key = AnimeWatchingStatus::KITSU_TO_TITLE[$status];

		$output = [];
		$output[$key] = $data;

		return $output;
	}

	/**
	 * Get data for the 'all' anime page
	 *
	 * @return array
	 */
	public function getAllLists(): array
	{
		$data =  $this->kitsuModel->getFullOrganizedAnimeList();

		foreach($data as $section => &$list)
		{
			$this->sortByName($list, 'anime');
		}

		return $data;
	}

	/**
	 * Get information about an anime from its slug
	 *
	 * @param string $slug
	 * @return AnimeType
	 */
	public function getAnime(string $slug): AnimeType
	{
		return $this->kitsuModel->getAnime($slug);
	}

	/**
	 * Get anime by its kitsu id
	 *
	 * @param string $animeId
	 * @return AnimeType
	 */
	public function getAnimeById(string $animeId): AnimeType
	{
		return $this->kitsuModel->getAnimeById($animeId);
	}

	/**
	 * Get recent watch history
	 *
	 * @return array
	 */
	public function getHistory(): array
	{
		return $this->kitsuModel->getAnimeHistory();
	}


}