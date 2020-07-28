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

namespace Aviat\AnimeClient\API\Kitsu;

use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Di\ContainerInterface;

use const Aviat\AnimeClient\USER_AGENT;

use Aviat\AnimeClient\API\APIRequestBuilder;

final class KitsuRequestBuilder extends APIRequestBuilder {
	use ContainerAware;

	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected string $baseUrl = 'https://kitsu.io/api/graphql';

	/**
	 * Valid HTTP request methods
	 * @var array
	 */
	protected array $validMethods = ['POST'];

	/**
	 * HTTP headers to send with every request
	 *
	 * @var array
	 */
	protected array $defaultHeaders = [
		'User-Agent' => USER_AGENT,
		'Accept' => 'application/json',
		'Content-Type' => 'application/json',
	];

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}
}