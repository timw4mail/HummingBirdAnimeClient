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

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\Kitsu;
use function Aviat\AnimeClient\getLocalImg;

use Aviat\AnimeClient\Types\User;
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transform user profile data for display
 *
 * @param array|object $profileData
 * @return User
 */
final class UserTransformer extends AbstractTransformer {
	public function transform(array|object $item): User
	{
		$item = (array)$item;
		$base = $item['data']['findProfileBySlug'] ?? [];
		$favorites = $base['favorites']['nodes'] ?? [];
		$stats = $base['stats'] ?? [];
		$waifu = (array_key_exists('waifu', $base)) ? [
			'label' => $base['waifuOrHusbando'],
			'character' => $base['waifu'],
		] : [];

		return User::from([
			'about' => $base['about'],
			'avatar' => $base['avatarImage']['original']['url'],
			'favorites' => $this->organizeFavorites($favorites),
			'location' => $base['location'],
			'name' => $base['name'],
			'slug' => $base['slug'],
			'stats' => $this->organizeStats($stats),
			'waifu' => $waifu,
			'website' => $base['siteLinks']['nodes'][0]['url'],
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

		foreach ($rawFavorites as $item)
		{
			$type = strtolower($item['item']['__typename']);
			$output[$type][$item['id']] = $item['item'];
		}

		return $output;
	}

	private function organizeStats(array $stats, array $data = []): array
	{
		$animeStats = [];
		$mangaStats = [];
		$otherStats = [];

		if (array_key_exists('animeAmountConsumed', $stats))
		{
			$animeStats = [
				'Time spent watching anime:' => Kitsu::friendlyTime($stats['animeAmountConsumed']['time']),
				'Anime series watched:' => number_format($stats['animeAmountConsumed']['media']),
				'Anime episodes watched:' => number_format($stats['animeAmountConsumed']['units']),
			];
		}

		if (array_key_exists('mangaAmountConsumed', $stats))
		{
			$mangaStats = [
				'Manga series read:' => number_format($stats['mangaAmountConsumed']['media']),
				'Manga chapters read:' => number_format($stats['mangaAmountConsumed']['units']),
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