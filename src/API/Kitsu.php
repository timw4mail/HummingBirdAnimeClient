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

namespace Aviat\AnimeClient\API;

use Aviat\AnimeClient\API\Kitsu\Enum\{
	AnimeAiringStatus,
	AnimeWatchingStatus,
	MangaReadingStatus
};
use DateTimeImmutable;

const AUTH_URL = 'https://kitsu.io/api/oauth/token';
const AUTH_USER_ID_KEY = 'kitsu-auth-userid';
const AUTH_TOKEN_CACHE_KEY = 'kitsu-auth-token';

/**
 * Data massaging helpers for the Kitsu API
 */
class Kitsu {
	const AUTH_URL = 'https://kitsu.io/api/oauth/token';
	const AUTH_USER_ID_KEY = 'kitsu-auth-userid';
	const AUTH_TOKEN_CACHE_KEY = 'kitsu-auth-token';

	/**
	 * Map of Kitsu status to label for select menus
	 *
	 * @return array
	 */
	public static function getStatusToSelectMap()
	{
		return [
			AnimeWatchingStatus::WATCHING => 'Currently Watching',
			AnimeWatchingStatus::PLAN_TO_WATCH => 'Plan to Watch',
			AnimeWatchingStatus::COMPLETED => 'Completed',
			AnimeWatchingStatus::ON_HOLD => 'On Hold',
			AnimeWatchingStatus::DROPPED => 'Dropped'
		];
	}

	/**
	 * Map of Kitsu Manga status to label for select menus
	 *
	 * @return array
	 */
	public static function getStatusToMangaSelectMap()
	{
		return [
			MangaReadingStatus::READING => 'Currently Reading',
			MangaReadingStatus::PLAN_TO_READ => 'Plan to Read',
			MangaReadingStatus::COMPLETED => 'Completed',
			MangaReadingStatus::ON_HOLD => 'On Hold',
			MangaReadingStatus::DROPPED => 'Dropped'
		];
	}

	/**
	 * Determine whether an anime is airing, finished airing, or has not yet aired
	 *
	 * @param string $startDate
	 * @param string $endDate
	 * @return string
	 */
	public static function getAiringStatus(string $startDate = null, string $endDate = null): string
	{
		$startAirDate = new DateTimeImmutable($startDate ?? 'tomorrow');
		$endAirDate = new DateTimeImmutable($endDate ?? 'next year');
		$now = new DateTimeImmutable();

		$isDoneAiring = $now > $endAirDate;
		$isCurrentlyAiring = ($now > $startAirDate) && ! $isDoneAiring;

		switch (true)
		{
			case $isCurrentlyAiring:
				return AnimeAiringStatus::AIRING;

			case $isDoneAiring:
				return AnimeAiringStatus::FINISHED_AIRING;

			default:
				return AnimeAiringStatus::NOT_YET_AIRED;
		}
	}

	/**
	 * Get the name and logo for the streaming service of the current link
	 *
	 * @param string $hostname
	 * @return array
	 */
	protected static function getServiceMetaData(string $hostname = null): array
	{
		switch($hostname)
		{
			case 'www.crunchyroll.com':
				return [
					'name' => 'Crunchyroll',
					'link' => true,
					'image' => 'streaming-logos/crunchyroll.svg',
				];

			case 'www.funimation.com':
				return [
					'name' => 'Funimation',
					'link' => true,
					'image' => 'streaming-logos/funimation.svg',
				];

			case 'www.hulu.com':
				return [
					'name' => 'Hulu',
					'link' => true,
					'image' => 'streaming-logos/hulu.svg',
				];

			// Default to Netflix, because the API links are broken,
			// and there's no other real identifier for Netflix
			default:
				return [
					'name' => 'Netflix',
					'link' => false,
					'image' => 'streaming-logos/netflix.svg',
				];
		}
	}

	/**
	 * Reorganize streaming links
	 *
	 * @param array $included
	 * @return array
	 */
	public static function parseStreamingLinks(array $included): array
	{
		if ( ! array_key_exists('streamingLinks', $included))
		{
			return [];
		}

		$links = [];

		foreach ($included['streamingLinks'] as $streamingLink)
		{
			$host = parse_url($streamingLink['url'], \PHP_URL_HOST);

			$links[] = [
				'meta' => static::getServiceMetaData($host),
				'link' => $streamingLink['url'],
				'subs' => $streamingLink['subs'],
				'dubs' => $streamingLink['dubs']
			];
		}

		return $links;
	}

	/**
	 * Reorganize streaming links for the current list item
	 *
	 * @param array $included
	 * @return array
	 */
	public static function parseListItemStreamingLinks(array $included, string $animeId): array
	{
		// Anime lists have a different structure to search through
		if (array_key_exists('anime', $included) && ! array_key_exists('streamingLinks', $included))
		{
			$links = [];
			$anime = $included['anime'][$animeId];

			if (count($anime['relationships']['streamingLinks']) > 0)
			{
				foreach ($anime['relationships']['streamingLinks'] as $streamingLink)
				{
					$host = parse_url($streamingLink['url'], \PHP_URL_HOST);

					$links[] = [
						'meta' => static::getServiceMetaData($host),
						'link' => $streamingLink['url'],
						'subs' => $streamingLink['subs'],
						'dubs' => $streamingLink['dubs']
					];
				}
			}

			return $links;
		}

		return [];
	}

	/**
	 * Filter out duplicate and very similar names from
	 *
	 * @param array $data The 'attributes' section of the api data response
	 * @return array List of alternate titles
	 */
	public static function filterTitles(array $data): array
	{
		// The 'canonical' title is always returned
		$valid = [$data['canonicalTitle']];

		if (array_key_exists('titles', $data))
		{
			foreach($data['titles'] as $alternateTitle)
			{
				if (self::titleIsUnique($alternateTitle, $valid))
				{
					$valid[] = $alternateTitle;
				}
			}
		}

		return $valid;
	}

	/**
	 * Determine if an alternate title is unique enough to list
	 *
	 * @param string $title
	 * @param array $existingTitles
	 * @return bool
	 */
	private static function titleIsUnique(string $title = null, array $existingTitles = []): bool
	{
		if (empty($title))
		{
			return false;
		}

		foreach($existingTitles as $existing)
		{
			$isSubset = stripos($existing, $title) !== FALSE;
			$diff = levenshtein($existing, $title);
			$onlydifferentCase = (mb_strtolower($existing) === mb_strtolower($title));

			if ($diff < 3 || $isSubset || $onlydifferentCase)
			{
				return false;
			}
		}

		return true;
	}
}