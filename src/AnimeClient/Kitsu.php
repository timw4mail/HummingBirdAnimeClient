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

namespace Aviat\AnimeClient;

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

	public const SECONDS_IN_MINUTE = 60;
	public const MINUTES_IN_HOUR = 60;
	public const MINUTES_IN_DAY = 1440;
	public const MINUTES_IN_YEAR = 525_600;

	/**
	 * Determine whether an anime is airing, finished airing, or has not yet aired
	 *
	 * @param string|null $startDate
	 * @param string|null $endDate
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

	/**
	 * Reformat the airing date range for an Anime
	 *
	 * @param string|null $startDate
	 * @param string|null $endDate
	 */
	public static function formatAirDates(string $startDate = NULL, string $endDate = NULL): string
	{
		if (empty($startDate))
		{
			return '';
		}

		$monthMap = [
			'01' => 'Jan',
			'02' => 'Feb',
			'03' => 'Mar',
			'04' => 'Apr',
			'05' => 'May',
			'06' => 'Jun',
			'07' => 'Jul',
			'08' => 'Aug',
			'09' => 'Sep',
			'10' => 'Oct',
			'11' => 'Nov',
			'12' => 'Dec',
		];

		[$startYear, $startMonth, $startDay] = explode('-', $startDate);

		if ($startDate === $endDate)
		{
			return "{$monthMap[$startMonth]} {$startDay}, {$startYear}";
		}

		if (empty($endDate))
		{
			return "{$monthMap[$startMonth]} {$startYear} - ";
		}

		[$endYear, $endMonth] = explode('-', $endDate);

		if ($startYear === $endYear)
		{
			return "{$monthMap[$startMonth]} - {$monthMap[$endMonth]} {$startYear}";
		}

		return "{$monthMap[$startMonth]} {$startYear} - {$monthMap[$endMonth]} {$endYear}";
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

	/**
	 * @return array<string, string>
	 */
	public static function mappingsToUrls(array $mappings, string $kitsuLink = ''): array
	{
		$output = [];

		$urlMap = [
			'ANIDB' => [
				'key' => 'AniDB',
				'url' => 'https://anidb.net/anime/{}',
			],
			'ANILIST_ANIME' => [
				'key' => 'Anilist',
				'url' => 'https://anilist.co/anime/{}/',
			],
			'ANILIST_MANGA' => [
				'key' => 'Anilist',
				'url' => 'https://anilist.co/anime/{}/',
			],
			'ANIMENEWSNETWORK' => [
				'key' => 'AnimeNewsNetwork',
				'url' => 'https://www.animenewsnetwork.com/encyclopedia/anime.php?id={}',
			],
			'MANGAUPDATES' => [
				'key' => 'MangaUpdates',
				'url' => 'https://www.mangaupdates.com/series.html?id={}',
			],
			'MYANIMELIST_ANIME' => [
				'key' => 'MyAnimeList',
				'url' => 'https://myanimelist.net/anime/{}',
			],
			'MYANIMELIST_CHARACTERS' => [
				'key' => 'MyAnimeList',
				'url' => 'https://myanimelist.net/character/{}',
			],
			'MYANIMELIST_MANGA' => [
				'key' => 'MyAnimeList',
				'url' => 'https://myanimelist.net/manga/{}',
			],
			'MYANIMELIST_PEOPLE' => [
				'key' => 'MyAnimeList',
				'url' => 'https://myanimelist.net/people/{}',
			],
		];

		foreach ($mappings as $mapping)
		{
			if ( ! array_key_exists($mapping['externalSite'], $urlMap))
			{
				continue;
			}

			$uMap = $urlMap[$mapping['externalSite']];
			$key = $uMap['key'];
			$url = str_replace('{}', $mapping['externalId'], $uMap['url']);


			$output[$key] = $url;
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
	 * @return mixed[]
	 */
	public static function parseStreamingLinks(array $nodes): array
	{
		if (empty($nodes))
		{
			return [];
		}

		$links = [];

		foreach ($nodes as $streamingLink)
		{
			$url = $streamingLink['url'];

			// 'Fix' links that start with the hostname,
			// rather than a protocol
			if ( ! str_contains($url, '//'))
			{
				$url = '//' . $url;
			}

			$host = parse_url($url, \PHP_URL_HOST);
			if ($host === FALSE)
			{
				return [];
			}

			$links[] = [
				'meta' => self::getServiceMetaData($host),
				'link' => $streamingLink['url'],
				'subs' => $streamingLink['subs'],
				'dubs' => $streamingLink['dubs']
			];
		}

		usort($links, static fn ($a, $b) => $a['meta']['name'] <=> $b['meta']['name']);

		return $links;
	}

	/**
	 * Get the list of titles
	 *
	 * @return mixed[]
	 */
	public static function getTitles(array $titles): array
	{
		$raw = array_unique([
			$titles['canonical'],
			...array_values($titles['localized']),
		]);

		return array_diff($raw,[$titles['canonical']]);
	}

	/**
	 * Filter out duplicate and very similar titles from a GraphQL response
	 *
	 * @return mixed[]
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

		// Don't return the canonical title
		array_shift($valid);

		return $valid;
	}

	/**
	 * Filter out duplicate and very similar titles from a GraphQL response
	 *
	 * @return mixed[]
	 */
	public static function getFilteredTitles(array $titles): array
	{
		// The 'canonical' title is always considered
		$valid = [$titles['canonical']];

		if (array_key_exists('localized', $titles) && is_array($titles['localized']))
		{
			foreach($titles['localized'] as $locale => $alternateTitle)
			{
				// Really don't care about languages that aren't english
				// or Japanese for titles
				if ( ! in_array($locale, ['en', 'en_us', 'en_jp', 'ja_jp']))
				{
					continue;
				}

				if (self::titleIsUnique($alternateTitle, $valid))
				{
					$valid[] = $alternateTitle;
				}
			}
		}

		// Don't return the canonical title
		array_shift($valid);

		return $valid;
	}

	/**
	 * Get the url of the posterImage from Kitsu, with fallbacks
	 */
	public static function getPosterImage(array $base, int $size = 1): string
	{
		$rawUrl =  $base['posterImage']['views'][$size]['url']
			?? $base['posterImage']['original']['url']
			?? '/public/images/placeholder.png';

		$parts = explode('?', $rawUrl);

		return ( empty($parts)) ? $rawUrl : $parts[0];
	}

	/**
	 * Get the name and logo for the streaming service of the current link
	 *
	 * @param string|null $hostname
	 * @return string[]|bool[]
	 */
	protected static function getServiceMetaData(string $hostname = NULL): array
	{
		$hostname = str_replace('www.', '', $hostname ?? '');

		$serviceMap = [
			'animelab.com' => [
				'name' => 'Animelab',
				'link' => TRUE,
				'image' => 'streaming-logos/animelab.svg',
			],
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
			'vrv.co' => [
				'name' => 'VRV',
				'link' => TRUE,
				'image' => 'streaming-logos/vrv.svg',
			]
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
			return $last ?? '';
		}

		return (count($parts) > 1)
			? implode(', ', $parts) . ", and {$last}"
			: "{$parts[0]}, {$last}";
	}

	/**
	 * Determine if an alternate title is unique enough to list
	 */
	protected static function titleIsUnique(?string $title = '', array $existingTitles = []): bool
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