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

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transformer for anime description page
 */
class AnimeTransformer extends AbstractTransformer {

    /**
     * Convert raw api response to a more
     * logical and workable structure
     *
     * @param  array  $item API library item
     * @return array
     */
    public function transform($item)
    {
    	sort($item['genres']);

        return [
            'title' => $item['canonicalTitle'],
			'en_title' => $item['titles']['en_jp'],
			'jp_title' => $item['titles']['ja_jp'],
			'cover_image' => $item['posterImage']['small'],
			'show_type' => $item['showType'],
			'episode_count' => $item['episodeCount'],
			'episode_length' => $item['episodeLength'],
			'synopsis' => $item['synopsis'],
			'age_rating' => $item['ageRating'],
			'age_rating_guide' => $item['ageRatingGuide'],
			'url' => "https://kitsu.io/anime/{$item['slug']}",
			'genres' => $item['genres'],
        ];
    }
}