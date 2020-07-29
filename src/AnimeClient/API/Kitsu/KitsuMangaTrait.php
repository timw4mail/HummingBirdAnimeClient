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
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Amp\Http\Client\Request;
use Aviat\AnimeClient\API\Enum\MangaReadingStatus\Kitsu as KitsuReadingStatus;
use Aviat\AnimeClient\API\JsonAPI;
use Aviat\AnimeClient\API\Kitsu as K;
use Aviat\AnimeClient\API\Kitsu\Transformer\MangaHistoryTransformer;
use Aviat\AnimeClient\API\Kitsu\Transformer\MangaListTransformer;
use Aviat\AnimeClient\API\Kitsu\Transformer\MangaTransformer;
use Aviat\AnimeClient\API\Mapping\MangaReadingStatus;
use Aviat\AnimeClient\API\ParallelAPIRequest;
use Aviat\AnimeClient\Enum\ListType;
use Aviat\AnimeClient\Types\MangaPage;
use Aviat\Banker\Exception\InvalidArgumentException;
use Aviat\Ion\Json;

/**
 * Manga-related list methods
 */
trait KitsuMangaTrait {
	/**
	 * @var MangaTransformer
	 */
	protected MangaTransformer $mangaTransformer;

	/**
	 * @var MangaListTransformer
	 */
	protected MangaListTransformer $mangaListTransformer;

	// -------------------------------------------------------------------------
	// ! Manga-specific methods
	// -------------------------------------------------------------------------

	/**
	 * Get information about a particular manga
	 *
	 * @param string $slug
	 * @return MangaPage
	 */
	public function getManga(string $slug): MangaPage
	{
		$baseData = $this->requestBuilder->runQuery('MangaDetails', [
			'slug' => $slug
		]);
		// $baseData = $this->getRawMediaData('manga', $slug);

		if (empty($baseData))
		{
			return MangaPage::from([]);
		}

		return $this->mangaTransformer->transform($baseData);
	}

	/**
	 * Get information about a particular manga
	 *
	 * @param string $mangaId
	 * @return MangaPage
	 */
	public function getMangaById(string $mangaId): MangaPage
	{
		$baseData = $this->requestBuilder->runQuery('MangaDetailsById', [
			'id' => $mangaId,
		]);
		// $baseData = $this->getRawMediaDataById('manga', $mangaId);
		return $this->mangaTransformer->transform($baseData);
	}

	/**
	 * Retrieve the data for the manga read history page
	 *
	 * @return array
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function getMangaHistory(): array
	{
		$key = K::MANGA_HISTORY_LIST_CACHE_KEY;
		$list = $this->cache->get($key, NULL);

		if ($list === NULL)
		{
			$raw = $this->getRawHistoryList('manga');
			$organized = JsonAPI::organizeData($raw);
			$organized = array_filter($organized, fn ($item) => array_key_exists('relationships', $item));

			$list = (new MangaHistoryTransformer())->transform($organized);

			$this->cache->set($key, $list);
		}

		return $list;
	}

	/**
	 * Get the manga list for the configured user
	 *
	 * @param string $status - The reading status by which to filter the list
	 * @param int $limit - The number of list items to fetch per page
	 * @param int $offset - The page offset
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getMangaList(string $status, int $limit = 200, int $offset = 0): array
	{
		$options = [
			'query' => [
				'filter' => [
					'user_id' => $this->getUserId(),
					'kind' => 'manga',
					'status' => $status,
				],
				'include' => 'media,media.categories,media.mappings',
				'page' => [
					'offset' => $offset,
					'limit' => $limit
				],
				'sort' => '-updated_at'
			]
		];

		$key = "kitsu-manga-list-{$status}";

		$list = $this->cache->get($key, NULL);

		if ($list === NULL)
		{
			$data = $this->jsonApiRequestBuilder->getRequest('library-entries', $options) ?? [];

			// Bail out on no data
			if (empty($data) || ( ! array_key_exists('included', $data)))
			{
				return [];
			}

			$included = JsonAPI::organizeIncludes($data['included']);
			$included = JsonAPI::inlineIncludedRelationships($included, 'manga');

			foreach($data['data'] as $i => &$item)
			{
				$item['included'] = $included;
			}
			unset($item);

			$list = $this->mangaListTransformer->transformCollection($data['data']);

			$this->cache->set($key, $list);
		}

		return $list;
	}

	/**
	 * Get the number of manga list items
	 *
	 * @param string $status - Optional status to filter by
	 * @return int
	 * @throws InvalidArgumentException
	 */
	public function getMangaListCount(string $status = '') : int
	{
		return $this->getListCount(ListType::MANGA, $status);
	}

	/**
	 * Get the full manga list
	 *
	 * @param array $options
	 * @return array
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function getFullRawMangaList(array $options = [
		'include' => 'manga.mappings'
	]): array
	{
		$status = $options['filter']['status'] ?? '';
		$count = $this->getMangaListCount($status);
		$size = static::LIST_PAGE_SIZE;
		$pages = ceil($count / $size);

		$requester = new ParallelAPIRequest();

		// Set up requests
		for ($i = 0; $i < $pages; $i++)
		{
			$offset = $i * $size;
			$requester->addRequest($this->getPagedMangaList($size, $offset, $options));
		}

		$responses = $requester->makeRequests();
		$output = [];

		foreach($responses as $response)
		{
			$data = Json::decode($response);
			$output[] = $data;
		}

		return array_merge_recursive(...$output);
	}

	/**
	 * Get all Manga lists
	 *
	 * @return array
	 * @throws ReflectionException
	 * @throws InvalidArgumentException
	 */
	public function getFullOrganizedMangaList(): array
	{
		$statuses = KitsuReadingStatus::getConstList();
		$output = [];
		foreach ($statuses as $status)
		{
			$mappedStatus = MangaReadingStatus::KITSU_TO_TITLE[$status];
			$output[$mappedStatus] = $this->getMangaList($status);
		}

		return $output;
	}

	/**
	 * Get the full manga list in paginated form
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param array $options
	 * @return Request
	 * @throws InvalidArgumentException
	 */
	public function getPagedMangaList(int $limit, int $offset = 0, array $options = [
		'include' => 'manga.mappings'
	]): Request
	{
		$defaultOptions = [
			'filter' => [
				'user_id' => $this->getUserId(),
				'kind' => 'manga'
			],
			'page' => [
				'offset' => $offset,
				'limit' => $limit
			],
			'sort' => '-updated_at'
		];
		$options = array_merge($defaultOptions, $options);

		return $this->jsonApiRequestBuilder->setUpRequest('GET', 'library-entries', ['query' => $options]);
	}

	/**
	 * Get the mal id for the manga represented by the kitsu id
	 * to enable updating MyAnimeList
	 *
	 * @param string $kitsuMangaId The id of the manga on Kitsu
	 * @return string|null Returns the mal id if it exists, otherwise null
	 */
	public function getMalIdForManga(string $kitsuMangaId): ?string
	{
		$options = [
			'query' => [
				'include' => 'mappings'
			]
		];
		$data = $this->jsonApiRequestBuilder->getRequest("manga/{$kitsuMangaId}", $options);
		$mappings = array_column($data['included'], 'attributes');

		foreach($mappings as $map)
		{
			if ($map['externalSite'] === 'myanimelist/manga')
			{
				return $map['externalId'];
			}
		}

		return NULL;
	}
}