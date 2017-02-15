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
					'logo' => '<svg class="streaming-logo" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg"><g fill="#F78B24" fill-rule="evenodd"><path d="M22.549 49.145c-.815-.077-2.958-.456-3.753-.663-6.873-1.79-12.693-6.59-15.773-13.009C1.335 31.954.631 28.807.633 24.788c.003-4.025.718-7.235 2.38-10.686 1.243-2.584 2.674-4.609 4.706-6.66 3.8-3.834 8.614-6.208 14.067-6.936 1.783-.239 5.556-.161 7.221.148 3.463.642 6.571 1.904 9.357 3.797 5.788 3.934 9.542 9.951 10.52 16.861.21 1.48.332 4.559.19 4.816-.077.14-.117-.007-.167-.615-.25-3.015-1.528-6.66-3.292-9.388C40.253 7.836 30.249 4.32 20.987 7.467c-7.15 2.43-12.522 8.596-13.997 16.06-.73 3.692-.51 7.31.658 10.882a21.426 21.426 0 0 0 13.247 13.518c1.475.515 3.369.944 4.618 1.047 1.496.122 1.119.239-.727.224-1.006-.008-2.013-.032-2.237-.053z"></path><path d="M27.685 46.1c-7.731-.575-14.137-6.455-15.474-14.204-.243-1.41-.29-4.047-.095-5.345 1.16-7.706 6.97-13.552 14.552-14.639 1.537-.22 4.275-.143 5.746.162 1.28.266 2.7.737 3.814 1.266l.865.411-.814.392c-2.936 1.414-4.748 4.723-4.323 7.892.426 3.173 2.578 5.664 5.667 6.56 1.112.322 2.812.322 3.925 0 1.438-.417 2.566-1.1 3.593-2.173.346-.362.652-.621.68-.576.027.046.106.545.176 1.11.171 1.395.07 4.047-.204 5.371-.876 4.218-3.08 7.758-6.463 10.374-3.2 2.476-7.434 3.711-11.645 3.399z"></path></g></svg>'
				];

			case 'www.funimation.com':
				return [
					'name' => 'Funimation',
					'link' => true,
					'logo' => '<svg class="streaming-logo" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg"><path d="M24.066.017a24.922 24.922 0 0 1 13.302 3.286 25.098 25.098 0 0 1 7.833 7.058 24.862 24.862 0 0 1 4.207 9.575c.82 4.001.641 8.201-.518 12.117a24.946 24.946 0 0 1-4.868 9.009 24.98 24.98 0 0 1-7.704 6.118 24.727 24.727 0 0 1-10.552 2.718A24.82 24.82 0 0 1 13.833 47.3c-5.815-2.872-10.408-8.107-12.49-14.25-2.162-6.257-1.698-13.375 1.303-19.28C5.483 8.07 10.594 3.55 16.602 1.435A24.94 24.94 0 0 1 24.066.017zm-8.415 33.31c.464 2.284 1.939 4.358 3.99 5.48 2.174 1.217 4.765 1.444 7.202 1.181 2.002-.217 3.986-.992 5.455-2.397 1.173-1.151 2.017-2.648 2.33-4.267-1.189-.027-2.378 0-3.566-.03-.568.082-1.137-.048-1.705.014-1.232.012-2.465.003-3.697-.01-.655.066-1.309-.035-1.963.013-1.166-.053-2.334.043-3.5-.025-1.515.08-3.03-.035-4.546.042z" fill="#411299" fill-rule="evenodd"></path></svg>'
				];

			case 'www.hulu.com':
				return [
					'name' => 'Hulu',
					'link' => true,
					'logo' => '<svg class="streaming-logo" viewBox="0 0 34 50" xmlns="http://www.w3.org/2000/svg"><path d="M22.222 13.889h-11.11V0H0v50h11.111V27.778c0-1.39 1.111-2.778 2.778-2.778h5.555c1.39 0 2.778 1.111 2.778 2.778V50h11.111V25c0-6.111-5-11.111-11.11-11.111z" fill="#8BC34A" fill-rule="evenodd"></path></svg>'
				];

			// Default to Netflix, because the API links are broken,
			// and there's no other real identifier for Netflix
			default:
				return [
					'name' => 'Netflix',
					'link' => false,
					'logo' => '<svg class="streaming-logo" viewBox="0 0 26 50" xmlns="http://www.w3.org/2000/svg"><path d="M.057.258C2.518.253 4.982.263 7.446.253c2.858 7.76 5.621 15.556 8.456 23.324.523 1.441 1.003 2.897 1.59 4.312.078-9.209.01-18.42.034-27.631h7.763v46.36c-2.812.372-5.637.627-8.457.957-1.203-3.451-2.396-6.902-3.613-10.348-1.796-5.145-3.557-10.302-5.402-15.428.129 8.954.015 17.912.057 26.871-2.603.39-5.227.637-7.815 1.119C.052 33.279.06 16.768.057.258z" fill="#E21221" fill-rule="evenodd"></path></svg>'
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