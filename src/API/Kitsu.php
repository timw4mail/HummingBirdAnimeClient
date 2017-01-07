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
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

use Aviat\AnimeClient\API\Kitsu\Enum\{AnimeAiringStatus, AnimeWatchingStatus};
use DateTimeImmutable;

/**
 * Constants and mappings for the Kitsu API
 */
class Kitsu {
	const AUTH_URL = 'https://kitsu.io/api/oauth/token';

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
	 * Determine whether an anime is airing, finished airing, or has not yet aired
	 *
	 * @param string $startDate
	 * @param string $endDate
	 * @return string
	 */
	public static function getAiringStatus(string $startDate = null, string $endDate = null): string
	{
		$startAirDate = new DateTimeImmutable($startDate ?? 'tomorrow');
		$endAirDate = new DateTimeImmutable($endDate ?? 'tomorrow');
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
	 * Reorganizes 'included' data to be keyed by
	 * 	type => [
	 * 		id => data/attributes,
	 * 	]
	 *
	 * @param array $includes
	 * @return array
	 */
	public static function organizeIncludes(array $includes): array
	{
		$organized = [];

		foreach ($includes as $item)
		{
			$type = $item['type'];
			$id = $item['id'];
			$organized[$type] = $organized[$type] ?? [];
			$organized[$type][$id] = $item['attributes'];

			if (array_key_exists('relationships', $item))
			{
				$organized[$type][$id]['relationships'] = self::organizeRelationships($item['relationships']);
			}
		}

		return $organized;
	}

	/**
	 * Reorganize relationship mappings to make them simpler to use
	 *
	 * Remove verbose structure, and just map:
	 * 	type => [ idArray ]
	 *
	 * @param array $relationships
	 * @return array
	 */
	public static function organizeRelationships(array $relationships): array
	{
		$organized = [];

		foreach($relationships as $key => $data)
		{
			if ( ! array_key_exists('data', $data))
			{
				continue;
			}

			$organized[$key] = $organized[$key] ?? [];

			foreach ($data['data'] as $item)
			{
				$organized[$key][] = $item['id'];
			}
		}

		return $organized;
	}

	/**
	 * Determine if an alternate title is unique enough to list
	 *
	 * @param string $title
	 * @param array $existingTitles
	 * @return bool
	 */
	private static function titleIsUnique(string $title = null, array $existingTitles): bool
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