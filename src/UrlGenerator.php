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

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;
use InvalidArgumentException;

/**
 * UrlGenerator class.
 */
class UrlGenerator extends RoutingBase {

	/**
	 * The current HTTP host
	 * @var string
	 */
	protected $host;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
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
	 *
	 * @param string ...$args
	 * @return string
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
	 *
	 * @param string $path
	 * @return string
	 */
	public function url(string $path): string
	{
		$path = trim($path, '/');

		$path = preg_replace('`{/.*?}`i', '', $path);

		// Remove any optional parameters from the route
		// and replace them with existing route parameters, if they exist
		$path_segments = explode('/', $path);
		$segment_count = count($path_segments);
		$segments = $this->segments();

		for ($i = 0; $i < $segment_count; $i++)
		{
			if ( ! array_key_exists($i + 1, $segments))
			{
				$segments[$i + 1] = '';
			}

			$path_segments[$i] = preg_replace('`{.*?}`', $segments[$i + 1], $path_segments[$i]);
		}
		$path = implode('/', $path_segments);

		$scheme = $this->config->get('secure_urls') !== FALSE ? 'https:' : 'http:';

		return "{$scheme}//{$this->host}/{$path}";
	}

	/**
	 * Full default path for the list pages
	 *
	 * @param string $type
	 * @throws InvalidArgumentException
	 * @return string
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