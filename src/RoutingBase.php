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
 * @copyright   2015 - 2019  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\Ion\Config;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;
use Aviat\Ion\Exception\ConfigException;
use Aviat\Ion\StringWrapper;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Base for routing/url classes
 */
class RoutingBase {

	use StringWrapper;

	/**
	 * Injection Container
	 * @var ContainerInterface $container
	 */
	protected $container;

	/**
	 * Config Object
	 * @var Config
	 */
	protected $config;

	/**
	 * Class wrapper for input superglobals
	 * @var ServerRequestInterface
	 */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws ConfigException
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->config = $container->get('config');
		$this->request = $container->get('request');
	}

	/**
	 * Get the current url path
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @return string
	 */
	public function path(): string
	{
		$path = $this->request->getUri()->getPath();
		$cleanedPath = $this->string($path)
			->replace('%20', '')
			->trim()
			->trimRight('/')
			->ensureLeft('/');

		return (string)$cleanedPath;
	}

	/**
	 * Get the url segments
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @return array
	 */
	public function segments(): array
	{
		$path = $this->path();
		return explode('/', $path);
	}

	/**
	 * Get a segment of the current url
	 *
	 * @param int $num
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @return string|null
	 */
	public function getSegment($num): ?string
	{
		$segments = $this->segments();
		return $segments[$num] ?? NULL;
	}

	/**
	 * Retrieve the last url segment
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @return string
	 */
	public function lastSegment(): string
	{
		$segments = $this->segments();
		return end($segments);
	}
}