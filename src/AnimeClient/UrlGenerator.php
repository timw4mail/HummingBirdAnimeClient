<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use InvalidArgumentException;

/**
 * UrlGenerator class.
 */
class UrlGenerator extends RoutingBase
{
	/**
	 * The current HTTP host
	 */
	protected string $host;

	/**
	 * Constructor
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->host = $container->get('request')->getServerParams()['HTTP_HOST'];
	}

	/**
	 * Get the base url for css/js/images
	 */
	public function assetUrl(string ...$args): string
	{
		$baseUrl = rtrim($this->url(''), '/')
			. $this->config->get('asset_path');

		array_unshift($args, $baseUrl);

		return implode('/', $args);
	}

	/**
	 * Generate a proper url from the path
	 */
	public function url(string $path): string
	{
		$path = trim($path, '/');

		$path = preg_replace('`{/.*?}`i', '', $path) ?? '';

		// Remove any optional parameters from the route
		// and replace them with existing route parameters, if they exist
		$pathSegments = explode('/', $path);
		$segmentCount = count($pathSegments);
		$segments = $this->segments();

		for ($i = 0; $i < $segmentCount; $i++)
		{
			if ( ! array_key_exists($i + 1, $segments))
			{
				$segments[$i + 1] = '';
			}

			$pathSegments[$i] = preg_replace('`{.*?}`', $segments[$i + 1], $pathSegments[$i] ?? '');
		}

		$path = implode('/', $pathSegments);

		$scheme = $this->config->get('secure_urls') !== FALSE ? 'https:' : 'http:';

		return "{$scheme}//{$this->host}/{$path}";
	}

	/**
	 * Full default path for the list pages
	 *
	 * @throws InvalidArgumentException
	 */
	public function defaultUrl(string $type): string
	{
		$type = trim($type);
		$defaultPath = $this->config->get("default_{$type}_list_path");

		if ($defaultPath !== NULL)
		{
			return $this->url("{$type}/{$defaultPath}");
		}

		throw new InvalidArgumentException("Invalid default type: '{$type}'");
	}
}

// End of UrlGenerator.php
