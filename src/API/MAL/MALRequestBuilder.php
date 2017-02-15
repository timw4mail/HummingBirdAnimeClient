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
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\MAL;

use Aviat\AnimeClient\API\{
	APIRequestBuilder,
	MAL as M,
	XML
};

class MALRequestBuilder extends APIRequestBuilder {

	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected $baseUrl = M::BASE_URL;

	/**
	 * HTTP headers to send with every request
	 *
	 * @var array
	 */
	protected $defaultHeaders = [
		'Accept' => 'text/xml',
		'Accept-Encoding' => 'gzip',
		'Content-type' => 'application/x-www-form-urlencoded',
		'User-Agent' => "Tim's Anime Client/4.0"
	];

	/**
	 * Valid HTTP request methos
	 * @var array
	 */
	protected $validMethods = ['GET', 'POST', 'DELETE'];
}