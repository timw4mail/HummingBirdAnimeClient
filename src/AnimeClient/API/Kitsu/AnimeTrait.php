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

namespace Aviat\AnimeClient\API\Kitsu;

use Amp\Http\Client\Request;
use Aviat\AnimeClient\API\Enum\AnimeWatchingStatus\Kitsu as KitsuWatchingStatus;
use Aviat\AnimeClient\API\JsonAPI;
use Aviat\AnimeClient\API\Kitsu as K;
use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeHistoryTransformer;
use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeListTransformer;
use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeTransformer;
use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\API\ParallelAPIRequest;
use Aviat\AnimeClient\Enum\ListType;
use Aviat\AnimeClient\Types\Anime;
use Aviat\Banker\Exception\InvalidArgumentException;
use Aviat\Ion\Json;

/**
 * Anime-related list methods
 */
trait AnimeTrait {
	/**
	 * Class to map anime list items
	 * to a common format used by
	 * templates
	 *
	 * @var AnimeListTransformer
	 */
	protected AnimeListTransformer $animeListTransformer;

	/**
	 * @var AnimeTransformer
	 */
	protected AnimeTransformer $animeTransformer;

	// -------------------------------------------------------------------------
	// ! Anime-specific methods
	// -------------------------------------------------------------------------

	/**
	 * Get information about a particular anime
	 *
	 * @param string $slug
	 * @return Anime
	 */
	public function getAnime(string $slug): Anime
	{
		$baseData = $this->requestBuilder->runQuery('AnimeDetails', [
			'slug' => $slug
		]);

		if (empty($baseData))
		{
			return Anime::from([]);
		}

		return $this->animeTransformer->transform($baseData);
	}

	/**
	 * Get information about a particular anime
	 *
	 * @param string $animeId
	 * @return Anime
	 */
	public function getAnimeById(string $animeId): Anime
	{
		$baseData = $this->requestBuilder->runQuery('AnimeDetailsById', [
			'id' => $animeId,
		]);
		return $this->animeTransformer->transform($baseData);
	}

	/**
	 * Retrieve the data for the anime watch history page
	 *
	 * @return array
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function getAnimeHistory(): array
	{
		$key = K::ANIME_HISTORY_LIST_CACHE_KEY;
		$list = $this->cache->get($key, NULL);

		if ($list === NULL)
		{
			$raw = $this->getRawHistoryList('anime');

			$list = (new AnimeHistoryTransformer())->transform($raw);

			$this->cache->set($key, $list);

		}

		return $list;
	}

	/**
	 * Get the anime list for the configured user
	 *
	 * @param string $status - The watching status to filter the list with
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getAnimeList(string $status): array
	{
		$key = "kitsu-anime-list-{$status}";

		$list = $this->cache->get($key, NULL);

		if ($list === NULL)
		{
			$data = $this->getRawAnimeList($status) ?? [];

			// Bail out on no data
			if (empty($data))
			{
				return [];
			}

			$included = JsonAPI::organizeIncludes($data['included']);
			$included = JsonAPI::inlineIncludedRelationships($included, 'anime');

			foreach($data['data'] as $i => &$item)
			{
				$item['included'] = $included;
			}
			unset($item);
			$transformed = $this->animeListTransformer->transformCollection($data['data']);
			$keyed = [];

			foreach($transformed as $item)
			{
				$keyed[$item['id']] = $item;
			}

			$list = $keyed;
			$this->cache->set($key, $list);
		}

		return $list;
	}

	/**
	 * Get the number of anime list items
	 *
	 * @param string $status - Optional status to filter by
	 * @return int
	 * @throws InvalidArgumentException
	 */
	public function getAnimeListCount(string $status = '') : int
	{
		return $this->getListCount(ListType::ANIME, $status);
	}

	/**
	 * Get the full anime list
	 *
	 * @param array $options
	 * @return array
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function getFullRawAnimeList(array $options = [
		'include' => 'anime.mappings'
	]): array
	{
		$status = $options['filter']['status'] ?? '';
		$count = $this->getAnimeListCount($status);
		$size = static::LIST_PAGE_SIZE;
		$pages = ceil($count / $size);

		$requester = new ParallelAPIRequest();

		// Set up requests
		for ($i = 0; $i < $pages; $i++)
		{
			$offset = $i * $size;
			$requester->addRequest($this->getPagedAnimeList($size, $offset, $options));
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
	 * Get all the anime entries, that are organized for output to html
	 *
	 * @return array
	 * @throws ReflectionException
	 * @throws InvalidArgumentException
	 */
	public function getFullOrganizedAnimeList(): array
	{
		$output = [];

		$statuses = KitsuWatchingStatus::getConstList();

		foreach ($statuses as $key => $status)
		{
			$mappedStatus = AnimeWatchingStatus::KITSU_TO_TITLE[$status];
			$output[$mappedStatus] = $this->getAnimeList($status) ?? [];
		}

		return $output;
	}

	/**
	 * Get the mal id for the anime represented by the kitsu id
	 * to enable updating MyAnimeList
	 *
	 * @param string $kitsuAnimeId The id of the anime on Kitsu
	 * @return string|null Returns the mal id if it exists, otherwise null
	 */
	public function getMalIdForAnime(string $kitsuAnimeId): ?string
	{
		$options = [
			'query' => [
				'include' => 'mappings'
			]
		];
		$data = $this->requestBuilder->getRequest("anime/{$kitsuAnimeId}", $options);

		if ( ! array_key_exists('included', $data))
		{
			return NULL;
		}

		$mappings = array_column($data['included'], 'attributes');

		foreach($mappings as $map)
		{
			if ($map['externalSite'] === 'myanimelist/anime')
			{
				return $map['externalId'];
			}
		}

		return NULL;
	}

	/**
	 * Get the full anime list in paginated form
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param array $options
	 * @return Request
	 * @throws InvalidArgumentException
	 */
	public function getPagedAnimeList(int $limit, int $offset = 0, array $options = [
		'include' => 'anime.mappings'
	]): Request
	{
		$defaultOptions = [
			'filter' => [
				'user_id' => $this->getUserId(),
				'kind' => 'anime'
			],
			'page' => [
				'offset' => $offset,
				'limit' => $limit
			],
			'sort' => '-updated_at'
		];
		$options = array_merge($defaultOptions, $options);

		return $this->requestBuilder->setUpRequest('GET', 'library-entries', ['query' => $options]);
	}

	/**
	 * Get the raw (unorganized) anime list for the configured user
	 *
	 * @param string $status - The watching status to filter the list with
	 * @return array
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function getRawAnimeList(string $status): array
	{
		$options = [
			'filter' => [
				'user_id' => $this->getUserId(),
				'kind' => 'anime',
				'status' => $status,
			],
			'include' => 'media,media.categories,media.mappings,anime.streamingLinks',
			'sort' => '-updated_at'
		];

		return $this->getFullRawAnimeList($options);
	}
}