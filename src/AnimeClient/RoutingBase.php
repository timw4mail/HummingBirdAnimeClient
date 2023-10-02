<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aura\Router\Generator;
use Aviat\Ion\ConfigInterface;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use Aviat\Ion\Exception\ConfigException;
use Aviat\Ion\Type\StringType;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Base for routing/url classes
 */
abstract class RoutingBase
{
	/**
	 * Config Object
	 */
	protected ConfigInterface $config;

	/**
	 * Class wrapper for input superglobals
	 */
	protected ServerRequestInterface $request;

	/**
	 * Aura url generator
	 */
	protected Generator $routerUrl;

	/**
	 * Constructor
	 *
	 * @throws ConfigException
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(protected ContainerInterface $container)
	{
		$this->config = $container->get('config');
		$this->request = $container->get('request');
		$this->routerUrl = $container->get('aura-router')->getGenerator();
	}

	/**
	 * Get the current url path
	 */
	public function path(): string
	{
		$path = $this->request->getUri()->getPath();
		$cleanedPath = StringType::from($path)
			->replace('%20', '')
			->trim()
			->trimRight('/')
			->ensureLeft('/');

		return (string) $cleanedPath;
	}

	/**
	 * Get the url segments
	 */
	public function segments(): array
	{
		$path = $this->path();

		return explode('/', $path);
	}

	/**
	 * Get a segment of the current url
	 */
	public function getSegment(int $num): ?string
	{
		$segments = $this->segments();

		return $segments[$num] ?? NULL;
	}

	/**
	 * Retrieve the last url segment
	 */
	public function lastSegment(): string
	{
		$segments = $this->segments();

		return end($segments);
	}
}
