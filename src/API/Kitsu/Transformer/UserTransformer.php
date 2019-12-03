<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use function Aviat\AnimeClient\getLocalImg;

use Aviat\AnimeClient\API\JsonAPI;
use Aviat\AnimeClient\Types\User;
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transform user profile data for display
 */
final class UserTransformer extends AbstractTransformer {
	public function transform($profileData): User
	{
		$orgData = JsonAPI::organizeData($profileData)[0];
		$attributes = $orgData['attributes'];

		$rels = $orgData['relationships'] ?? [];
		$favorites = array_key_exists('favorites', $rels) ? $rels['favorites'] : [];

		$stats = [];
		foreach ($rels['stats'] as $sid => &$item)
		{
			$key = $item['attributes']['kind'];
			$stats[$key] = $item['attributes']['statsData'];
			unset($item);
		}

		$waifu = [];
		if (array_key_exists('waifu', $rels))
		{
			$waifu = [
				'label' => $attributes['waifuOrHusbando'],
				'character' => $rels['waifu']['attributes'],
			];
		}

		return new User([
			'about' => $attributes['about'],
			'avatar' => getLocalImg($attributes['avatar']['original'], FALSE),
			'favorites' => $this->organizeFavorites($favorites),
			'location' => $attributes['location'],
			'name' => $attributes['name'],
			'slug' => $attributes['slug'],
			'stats' => $this->organizeStats($stats, $attributes),
			'waifu' => $waifu,
			'website' => $attributes['website'],
		]);
	}

	/**
	 * Reorganize favorites data to be more useful
	 *
	 * @param array $rawFavorites
	 * @return array
	 */
	private function organizeFavorites(array $rawFavorites): array
	{
		$output = [];

		unset($rawFavorites['data']);

		foreach ($rawFavorites as $item)
		{
			$rank = $item['attributes']['favRank'];
			foreach ($item['relationships']['item'] as $key => $fav)
			{
				$output[$key] = $output[$key] ?? [];
				foreach ($fav as $id => $data)
				{
					$output[$key][$rank] = array_merge(['id' => $id], $data['attributes']);
				}

				ksort($output[$key]);
			}
		}

		return $output;
	}

	/**
	 * Format the time spent on anime in a more readable format
	 *
	 * @param int $seconds
	 * @return string
	 */
	private function formatAnimeTime(int $seconds): string
	{
		// All the seconds left
		$remSeconds = $seconds % 60;
		$minutes = ($seconds - $remSeconds) / 60;

		$minutesPerDay = 1440;
		$minutesPerYear = $minutesPerDay * 365;

		// Minutes short of a year
		$years = (int)floor($minutes / $minutesPerYear);
		$minutes %= $minutesPerYear;

		// Minutes short of a day
		$extraMinutes = $minutes % $minutesPerDay;
		$days = ($minutes - $extraMinutes) / $minutesPerDay;

		// Minutes short of an hour
		$remMinutes = $extraMinutes % 60;
		$hours = ($extraMinutes - $remMinutes) / 60;

		$output = "{$days} days, {$hours} hours, {$remMinutes} minutes, and {$remSeconds} seconds.";

		if ($years > 0)
		{
			$output = "{$years} year(s),{$output}";
		}

		return $output;
	}

	private function organizeStats($stats, $data = []): array
	{
		$animeStats = [];
		$mangaStats = [];
		$otherStats = [];

		if (array_key_exists('anime-amount-consumed', $stats))
		{
			$animeStats = [
				'Time spent watching anime:' => $this->formatAnimeTime($stats['anime-amount-consumed']['time']),
				'Anime series watched:' => number_format($stats['anime-amount-consumed']['media']),
				'Anime episodes watched:' => number_format($stats['anime-amount-consumed']['units']),
			];
		}

		if (array_key_exists('manga-amount-consumed', $stats))
		{
			$mangaStats = [
				'Manga series read:' => number_format($stats['manga-amount-consumed']['media']),
				'Manga chapters read:' => number_format($stats['manga-amount-consumed']['units']),
			];
		}

		if ( ! empty($data))
		{
			$otherStats = [
				'Posts:' => number_format($data['postsCount']),
				'Comments:' => number_format($data['commentsCount']),
				'Media Rated:' => number_format($data['ratingsCount']),
			];
		}

		return array_merge($animeStats, $mangaStats, $otherStats);
	}
}