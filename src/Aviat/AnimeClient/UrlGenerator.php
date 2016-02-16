<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */
namespace Aviat\AnimeClient;

use Aviat\Ion\Di\ContainerInterface;

/**
 * UrlGenerator class.
 */
class UrlGenerator extends RoutingBase {

	/**
	 * The current HTTP host
	 */
	protected $host;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->host = $container->get('request')->getServerParams()['HTTP_HOST'];
	}

	/**
	 * Get the base url for css/js/images
	 *
	 * @return string
	 */
	public function asset_url(/*...*/)
	{
		$args = func_get_args();
		$base_url = rtrim($this->url(""), '/');

		$base_url = "{$base_url}" . $this->__get("asset_path");

		array_unshift($args, $base_url);

		return implode("/", $args);
	}

	/**
	 * Get the base url from the config
	 *
	 * @param string $type - (optional) The controller
	 * @return string
	 */
	public function base_url($type = "anime")
	{
		$config_path = trim($this->__get("{$type}_path"), "/");

		$path = ($config_path !== '') ? $config_path : "";

		return implode("/", ['/', $this->host, $path]);
	}

	/**
	 * Generate a proper url from the path
	 *
	 * @param string $path
	 * @return string
	 */
	public function url($path)
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
				$segments[$i + 1] = "";
			}

			$path_segments[$i] = preg_replace('`{.*?}`i', $segments[$i + 1], $path_segments[$i]);
		}
		$path = implode('/', $path_segments);

		return "//{$this->host}/{$path}";
	}

	/**
	 * Full default path for the list pages
	 *
	 * @param string $type
	 * @return string
	 */
	public function default_url($type)
	{
		$type = trim($type);
		$default_path = $this->__get("default_{$type}_list_path");

		if ( ! is_null($default_path))
		{
			return $this->url("{$type}/{$default_path}");
		}

		throw new \InvalidArgumentException("Invalid default type: '{$type}'");
	}

	/**
	 * Generate full url path from the route path based on config
	 *
	 * @param string $path - (optional) The route path
	 * @param string $type - (optional) The controller (anime or manga), defaults to anime
	 * @return string
	 */
	public function full_url($path = "", $type = "anime")
	{
		$config_default_route = $this->__get("default_{$type}_path");

		// Remove beginning/trailing slashes
		$path = trim($path, '/');

		// Set the default view
		if ($path === '')
		{
			$path .= trim($config_default_route, '/');
			if ($this->__get('default_to_list_view'))
			{
				$path .= '/list';
			}
		}

		return $this->url($path);
	}
}
// End of UrlGenerator.php