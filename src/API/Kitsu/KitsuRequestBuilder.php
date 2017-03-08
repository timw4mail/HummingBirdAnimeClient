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
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Aviat\AnimeClient\API\{
	APIRequestBuilder,
	Kitsu as K
};
use Aviat\Ion\Json;

class KitsuRequestBuilder extends APIRequestBuilder {

	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected $baseUrl = "https://kitsu.io/api/edge/";

	/**
	 * HTTP headers to send with every request
	 *
	 * @var array
	 */
	protected $defaultHeaders = [
		'User-Agent' => "Tim's Anime Client/4.0",
		'Accept-Encoding' => 'application/vnd.api+json',
		'Content-Type' => 'application/vnd.api+json',
		'client_id' => 'dd031b32d2f56c990b1425efe6c42ad847e7fe3ab46bf1299f05ecd856bdb7dd',
		'client_secret' => '54d7307928f63414defd96399fc31ba847961ceaecef3a5fd93144e960c0e151',
	];
}