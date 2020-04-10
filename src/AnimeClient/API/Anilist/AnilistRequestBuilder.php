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

namespace Aviat\AnimeClient\API\Anilist;

use const Aviat\AnimeClient\USER_AGENT;

use Aviat\AnimeClient\API\APIRequestBuilder;

final class AnilistRequestBuilder extends APIRequestBuilder {

	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected $baseUrl = 'https://graphql.anilist.co';

	/**
	 * Valid HTTP request methods
	 * @var array
	 */
	protected $validMethods = ['POST'];

	/**
	 * HTTP headers to send with every request
	 *
	 * @var array
	 */
	protected $defaultHeaders = [
		'User-Agent' => USER_AGENT,
		'Accept' => 'application/json',
		'Content-Type' => 'application/json',
	];
}