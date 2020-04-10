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

namespace Aviat\AnimeClient\API;

use Aviat\AnimeClient\API\Kitsu\Enum\AnimeAiringStatus;
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
	 * Reorganize streaming links
	 *
	 * @param array $included
	 * @return array
	 */
	public static function parseStreamingLinks(array $included): array
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

		usort($links, function ($a, $b) {
			return $a['meta']['name'] <=> $b['meta']['name'];
		});

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
				return static::parseStreamingLinks($anime['relationships']);
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