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

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\API\{
	Enum\MangaReadingStatus\Title,
	Mapping\MangaReadingStatus,
};
use Aviat\AnimeClient\Types\{
	MangaPage
};

/**
 * Model for handling requests dealing with the manga list
 */
class Manga extends API
{
	use MediaTrait;

	protected string $type = 'manga';

	/**
	 * Get a category out of the full list
	 *
	 * @return mixed[]
	 */
	public function getList(string $status): array
	{
		if ($status === 'All')
		{
			$data = $this->kitsuModel->getFullOrganizedMangaList();

			foreach ($data as &$section)
			{
				$this->sortByName($section, 'manga');
			}

			return $data;
		}

		$APIstatus = MangaReadingStatus::TITLE_TO_KITSU[$status];
		$data = $this->mapByStatus($this->kitsuModel->getMangaList($APIstatus));
		$this->sortByName($data[$status], 'manga');

		return $data[$status];
	}

	/**
	 * Get the details of a manga
	 */
	public function getManga(string $manga_id): MangaPage
	{
		return $this->kitsuModel->getManga($manga_id);
	}

	/**
	 * Get the details of a random manga
	 */
	public function getRandomManga(): MangaPage
	{
		return $this->kitsuModel->getRandomManga();
	}

	/**
	 * Get anime by its kitsu id
	 */
	public function getMangaById(string $animeId): MangaPage
	{
		return $this->kitsuModel->getMangaById($animeId);
	}

	/**
	 * Get recent reading history
	 *
	 * @return mixed[]
	 */
	public function getHistory(): array
	{
		return $this->kitsuModel->getMangaHistory();
	}

	/**
	 * Map transformed anime data to be organized by reading status
	 *
	 * @return array<string, mixed[]>
	 */
	private function mapByStatus(array $data): array
	{
		$output = [
			Title::READING => [],
			Title::PLAN_TO_READ => [],
			Title::ON_HOLD => [],
			Title::DROPPED => [],
			Title::COMPLETED => [],
		];

		foreach ($data as $entry)
		{
			$statusMap = MangaReadingStatus::KITSU_TO_TITLE;
			$key = $statusMap[$entry['reading_status']];
			$output[$key][] = $entry;
		}

		unset($entry);

		return $output;
	}
}

// End of MangaModel.php
