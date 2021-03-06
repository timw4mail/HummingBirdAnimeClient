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

namespace Aviat\AnimeClient;

use Aviat\Ion\ConfigInterface;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;
use Aviat\Ion\Exception\ConfigException;
use Aviat\Ion\Type\StringType;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Base for routing/url classes
 */
class RoutingBase {

	/**
	 * Injection Container
	 * @var ContainerInterface $container
	 */
	protected ContainerInterface $container;

	/**
	 * Config Object
	 * @var ConfigInterface
	 */
	protected ConfigInterface $config;

	/**
	 * Class wrapper for input superglobals
	 * @var ServerRequestInterface
	 */
	protected ServerRequestInterface $request;

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
	 *
	 * @return string
	 */
	public function path(): string
	{
		$path = $this->request->getUri()->getPath();
		$cleanedPath = StringType::from($path)
			->replace('%20', '')
			->trim()
			->trimRight('/')
			->ensureLeft('/');

		return (string)$cleanedPath;
	}

	/**
	 * Get the url segments
	 *
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
	 *
	 * @return string|null
	 */
	public function getSegment(int $num): ?string
	{
		$segments = $this->segments();
		return $segments[$num] ?? NULL;
	}

	/**
	 * Retrieve the last url segment
	 *
	 * @return string
	 */
	public function lastSegment(): string
	{
		$segments = $this->segments();
		return end($segments);
	}
}