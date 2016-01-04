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
 * Helper object to manage menu creation and selection
 */
class MenuGenerator extends UrlGenerator {

	use \Aviat\Ion\StringWrapper;
	use \Aviat\Ion\ArrayWrapper;

	/**
	 * Html generation helper
	 *
	 * @var Aura\Html\HelperLocator
	 */
	protected $helper;

	/**
	 * Request object
	 *
	 * @var Aura\Web\Request
	 */
	protected $request;

	/**
	 * Create menu generator
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->helper = $container->get('html-helper');
		$this->request = $container->get('request');
	}

	/**
	 * Generate the full menu structure from the config files
	 *
	 * @param array $menus
	 * @return array
	 */
	protected function parse_config(array $menus)
	{
		$parsed = [];

		foreach ($menus as $name => $menu)
		{
			$parsed[$name] = [];
			foreach ($menu['items'] as $path_name => $partial_path)
			{
				$title = (string)$this->string($path_name)->humanize()->titleize();
				$parsed[$name][$title] = (string)$this->string($menu['route_prefix'])->append($partial_path);
			}
		}

		return $parsed;
	}

	/**
	 * Generate the html structure of the menu selected
	 *
	 * @param string $menu
	 * @return string
	 */
	public function generate($menu)
	{
		$menus = $this->config->get('menus');
		$parsed_config = $this->parse_config($menus);

		// Bail out early on invalid menu
		if ( ! $this->arr($parsed_config)->has_key($menu))
		{
			return '';
		}

		$menu_config = $parsed_config[$menu];

		foreach ($menu_config as $title => $path)
		{
			$has = $this->string($path)->contains($this->path());
			$selected = ($has && strlen($path) >= strlen($this->path()));

			$link = $this->helper->a($this->url($path), $title);

			$attrs = ($selected)
				? ['class' => 'selected']
				: [];

			$this->helper->ul()->rawItem($link, $attrs);
		}

		// Create the menu html
		return $this->helper->ul();
	}
}
// End of MenuGenerator.php