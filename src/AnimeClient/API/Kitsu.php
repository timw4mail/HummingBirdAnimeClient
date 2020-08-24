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

namespace Aviat\AnimeClient\API;

use Aviat\AnimeClient\API\Kitsu\Enum\AnimeAiringStatus;
use Aviat\AnimeClient\API\Kitsu\Enum\MangaPublishingStatus;
use DateTimeImmutable;

/**
 * Data massaging helpers for the Kitsu API
 */
final class Kitsu {
	public const AUTH_URL = 'https://kitsu.io/api/oauth/token';
	public const AUTH_USER_ID_KEY = 'kitsu-auth-userid';
	public const AUTH_TOKEN_CACHE_KEY = 'kitsu-auth-token';
	public const AUTH_TOKEN_EXP_CACHE_KEY = 'kitsu-auth-token-expires';
	public const AUTH_TOKEN_REFRESH_CACHE_KEY = 'kitsu-auth-token-refresh';
	public const ANIME_HISTORY_LIST_CACHE_KEY = 'kitsu-anime-history-list';
	public const MANGA_HISTORY_LIST_CACHE_KEY = 'kitsu-manga-history-list';

	public const GRAPHQL_ENDPOINT = 'https://kitsu.io/api/graphql';
	public const JSON_API_ENDPOINT = 'https://kitsu.io/api/edge/';

	public const SECONDS_IN_MINUTE = 60;
	public const MINUTES_IN_HOUR = 60;
	public const MINUTES_IN_DAY = 1440;
	public const MINUTES_IN_YEAR = 525_600;

	/**
	 * Determine whether an anime is airing, finished airing, or has not yet aired
	 *
	 * @param string $startDate
	 * @param string $endDate
	 * @return string
	 */
	public static function getAiringStatus(string $startDate = NULL, string $endDate = NULL): string
	{
		$startAirDate = new DateTimeImmutable($startDate ?? 'tomorrow');
		$endAirDate = new DateTimeImmutable($endDate ?? 'next year');
		$now = new DateTimeImmutable();

		$isDoneAiring = $now > $endAirDate;
		$isCurrentlyAiring = ($now > $startAirDate) && ! $isDoneAiring;

		if ($isCurrentlyAiring)
		{
			return AnimeAiringStatus::AIRING;
		}

		if ($isDoneAiring)
		{
			return AnimeAiringStatus::FINISHED_AIRING;
		}

		return AnimeAiringStatus::NOT_YET_AIRED;
	}

	public static function getPublishingStatus(string $kitsuStatus, string $startDate = NULL, string $endDate = NULL): string
	{
		$startPubDate = new DateTimeImmutable($startDate ?? 'tomorrow');
		$endPubDate = new DateTimeImmutable($endDate ?? 'next year');
		$now = new DateTimeImmutable();

		$isDone = $now > $endPubDate;
		$isCurrent = ($now > $startPubDate) && ! $isDone;

		if ($kitsuStatus === 'CURRENT' || $isCurrent)
		{
			return MangaPublishingStatus::CURRENT;
		}

		if ($kitsuStatus === 'FINISHED' || $isDone)
		{
			return MangaPublishingStatus::FINISHED;
		}

		return MangaPublishingStatus::NOT_YET_PUBLISHED;
	}

	public static function mappingsToUrls(array $mappings, string $kitsuLink = ''): array
	{
		$output = [];
		foreach ($mappings as $mapping)
		{
			switch ($mapping['externalSite'])
			{
				case 'ANIDB':
					$output['AniDB'] = "https://anidb.net/anime/{$mapping['externalId']}";
				break;

				case 'ANILIST_ANIME':
					$output['Anilist'] = "https://anilist.co/anime/{$mapping['externalId']}/";
				break;

				case 'ANILIST_MANGA':
					$output['Anilist'] = "https://anilist.co/manga/{$mapping['externalId']}/";
				break;

				case 'MYANIMELIST_ANIME':
					$output['MyAnimeList'] = "https://myanimelist.net/anime/{$mapping['externalId']}";
				break;

				case 'MYANIMELIST_MANGA':
					$output['MyAnimeList'] = "https://myanimelist.net/manga/{$mapping['externalId']}";
				break;

				default:
					continue 2;
			}
		}

		if ($kitsuLink !== '')
		{
			$output['Kitsu'] = $kitsuLink;
		}

		ksort($output);

		return $output;
	}

	/**
	 * Reorganize streaming links
	 *
	 * @param array $included
	 * @return array
	 */
	public static function oldParseStreamingLinks(array $included): array
	{
		if (
			( ! array_key_exists('streamingLinks', $included)) ||
			count($included['streamingLinks']) === 0
		)
		{
			return [];
		}

		$links = [];

		foreach ($included['streamingLinks'] as $streamingLink)
		{
			$url = $streamingLink['url'];

			// 'Fix' links that start with the hostname,
			// rather than a protocol
			if (strpos($url, '//') === FALSE)
			{
				$url = '//' . $url;
			}

			$host = parse_url($url, \PHP_URL_HOST);

			$links[] = [
				'meta' => static::getServiceMetaData($host),
				'link' => $streamingLink['url'],
				'subs' => $streamingLink['subs'],
				'dubs' => $streamingLink['dubs']
			];
		}

		usort($links, fn ($a, $b) => $a['meta']['name'] <=> $b['meta']['name']);

		return $links;
	}

	/**
	 * Reorganize streaming links
	 *
	 * @param array $nodes
	 * @return array
	 */
	public static function parseStreamingLinks(array $nodes): array
	{
		if (count($nodes) === 0)
		{
			return [];
		}

		$links = [];

		foreach ($nodes as $streamingLink)
		{
			$url = $streamingLink['url'];

			// 'Fix' links that start with the hostname,
			// rather than a protocol
			if (strpos($url, '//') === FALSE)
			{
				$url = '//' . $url;
			}

			$host = parse_url($url, \PHP_URL_HOST);

			$links[] = [
				'meta' => static::getServiceMetaData($host),
				'link' => $streamingLink['url'],
				'subs' => $streamingLink['subs'],
				'dubs' => $streamingLink['dubs']
			];
		}

		usort($links, fn ($a, $b) => $a['meta']['name'] <=> $b['meta']['name']);

		return $links;
	}

	/**
	 * Reorganize streaming links for the current list item
	 *
	 * @param array $included
	 * @param string $animeId
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
				return static::oldParseStreamingLinks($anime['relationships']);
			}

			return $links;
		}

		return [];
	}

	/**
	 * Get the list of titles
	 *
	 * @param array $data
	 * @return array
	 */
	public static function oldGetTitles(array $data): array
	{
		$raw = array_unique([
			$data['canonicalTitle'],
			...array_values($data['titles']),
			...array_values($data['abbreviatedTitles'] ?? []),
		]);

		return array_diff($raw,[$data['canonicalTitle']]);
	}

	/**
	 * Get the list of titles
	 *
	 * @param array $titles
	 * @return array
	 */
	public static function getTitles(array $titles): array
	{
		$raw = array_unique([
			$titles['canonical'],
			...array_values($titles['localized']),
			// ...array_values($data['abbreviatedTitles'] ?? []),
		]);

		return array_diff($raw,[$titles['canonical']]);
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

		if (array_key_exists('titles', $data) && is_array($data['titles']))
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
	 * Filter out duplicate and very similar titles from a GraphQL response
	 *
	 * @param array $titles
	 * @return array
	 */
	public static function filterLocalizedTitles(array $titles): array
	{
		// The 'canonical' title is always considered
		$valid = [$titles['canonical']];

		foreach (['alternatives', 'localized'] as $search)
		{
			if (array_key_exists($search, $titles) && is_array($titles[$search]))
			{
				foreach($titles[$search] as $alternateTitle)
				{
					if (self::titleIsUnique($alternateTitle, $valid))
					{
						$valid[] = $alternateTitle;
					}
				}
			}
		}

		// Don't return the canonical titles
		array_shift($valid);

		return $valid;
	}

	/**
	 * Filter out duplicate and very similar titles from a GraphQL response
	 *
	 * @param array $titles
	 * @return array
	 */
	public static function getFilteredTitles(array $titles): array
	{
		// The 'canonical' title is always considered
		$valid = [$titles['canonical']];

		if (array_key_exists('localized', $titles) && is_array($titles['localized']))
		{
			foreach($titles['localized'] as $alternateTitle)
			{
				if (self::titleIsUnique($alternateTitle, $valid))
				{
					$valid[] = $alternateTitle;
				}
			}
		}

		// Don't return the canonical titles
		array_shift($valid);

		return $valid;
	}

	/**
	 * Get the name and logo for the streaming service of the current link
	 *
	 * @param string $hostname
	 * @return array
	 */
	protected static function getServiceMetaData(string $hostname = NULL): array
	{
		$hostname = str_replace('www.', '', $hostname);

		$serviceMap = [
			'amazon.com' => [
				'name' => 'Amazon Prime',
				'link' => TRUE,
				'image' => 'streaming-logos/amazon.svg',
			],
			'crunchyroll.com' => [
				'name' => 'Crunchyroll',
				'link' => TRUE,
				'image' => 'streaming-logos/crunchyroll.svg',
			],
			'daisuki.net' => [
				'name' => 'Daisuki',
				'link' => TRUE,
				'image' => 'streaming-logos/daisuki.svg'
			],
			'funimation.com' => [
				'name' => 'Funimation',
				'link' => TRUE,
				'image' => 'streaming-logos/funimation.svg',
			],
			'hidive.com' => [
				'name' => 'Hidive',
				'link' => TRUE,
				'image' => 'streaming-logos/hidive.svg',
			],
			'hulu.com' => [
				'name' => 'Hulu',
				'link' => TRUE,
				'image' => 'streaming-logos/hulu.svg',
			],
			'tubitv.com' => [
				'name' => 'TubiTV',
				'link' => TRUE,
				'image' => 'streaming-logos/tubitv.svg',
			],
			'viewster.com' => [
				'name' => 'Viewster',
				'link' => TRUE,
				'image' => 'streaming-logos/viewster.svg'
			],
		];

		if (array_key_exists($hostname, $serviceMap))
		{
			return $serviceMap[$hostname];
		}

		// Default to Netflix, because the API links are broken,
		// and there's no other real identifier for Netflix
		return [
			'name' => 'Netflix',
			'link' => FALSE,
			'image' => 'streaming-logos/netflix.svg',
		];
	}

	/**
	 * Convert a time in seconds to a more human-readable format
	 *
	 * @param int $seconds
	 * @return string
	 */
	public static function friendlyTime(int $seconds): string
	{
		// All the seconds left
		$remSeconds = $seconds % self::SECONDS_IN_MINUTE;
		$minutes = ($seconds - $remSeconds) / self::SECONDS_IN_MINUTE;

		// Minutes short of a year
		$years = (int)floor($minutes / self::MINUTES_IN_YEAR);
		$minutes %= self::MINUTES_IN_YEAR;

		// Minutes short of a day
		$extraMinutes = $minutes % self::MINUTES_IN_DAY;
		$days = ($minutes - $extraMinutes) / self::MINUTES_IN_DAY;

		// Minutes short of an hour
		$remMinutes = $extraMinutes % self::MINUTES_IN_HOUR;
		$hours = ($extraMinutes - $remMinutes) / self::MINUTES_IN_HOUR;

		$parts = [];
		foreach ([
			'year' => $years,
			'day' => $days,
			'hour' => $hours,
			'minute' => $remMinutes,
			'second' => $remSeconds
	 	] as $label => $value)
		{
			if ($value === 0)
			{
				continue;
			}

			if ($value > 1)
			{
				$label .= 's';
			}

			$parts[] = "{$value} {$label}";
		}

		$last = array_pop($parts);

		if (empty($parts))
		{
			return $last;
		}

		return (count($parts) > 1)
			? implode(', ', $parts) . ", and {$last}"
			: "{$parts[0]}, {$last}";
	}

	/**
	 * Determine if an alternate title is unique enough to list
	 *
	 * @param string $title
	 * @param array $existingTitles
	 * @return bool
	 */
	private static function titleIsUnique(string $title = NULL, array $existingTitles = []): bool
	{
		if (empty($title))
		{
			return FALSE;
		}

		foreach($existingTitles as $existing)
		{
			$isSubset = mb_substr_count($existing, $title) > 0;
			$diff = levenshtein(mb_strtolower($existing), mb_strtolower($title));

			if ($diff <= 4 || $isSubset || mb_strlen($title) > 45 || mb_strlen($existing) > 50)
			{
				return FALSE;
			}
		}

		return TRUE;
	}
}