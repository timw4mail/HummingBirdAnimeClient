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

use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\Types\Anime as AnimeType;

/**
 * Model for handling requests dealing with the anime list
 */
class Anime extends API
{
	use MediaTrait;

	protected string $type = 'anime';

	/**
	 * Get a category out of the full list
	 *
	 * @return array<string, mixed>
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
	 * @return mixed[]
	 */
	public function getAllLists(): array
	{
		$data = $this->kitsuModel->getFullOrganizedAnimeList();

		foreach ($data as &$list)
		{
			$this->sortByName($list, 'anime');
		}

		return $data;
	}

	/**
	 * Get information about an anime from its slug
	 */
	public function getAnime(string $slug): AnimeType
	{
		return $this->kitsuModel->getAnime($slug);
	}

	/**
	 * Get information about a random anime
	 */
	public function getRandomAnime(): AnimeType
	{
		return $this->kitsuModel->getRandomAnime();
	}

	/**
	 * Get anime by its kitsu id
	 */
	public function getAnimeById(string $animeId): AnimeType
	{
		return $this->kitsuModel->getAnimeById($animeId);
	}

	/**
	 * Get recent watch history
	 *
	 * @return mixed[]
	 */
	public function getHistory(): array
	{
		return $this->kitsuModel->getAnimeHistory();
	}
}
