<?php

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
	 * Menu config array
	 *
	 * @var array
	 */
	protected $menus;

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
		$this->menus = $this->config->get('menus');
		$this->helper = $container->get('html-helper');
		$this->request = $container->get('request');
	}

	/**
	 * Generate the full menu structure from the config files
	 *
	 * @return array
	 */
	protected function parse_config()
	{
		// Note: Children menus have urls based on the
		// current url path
		/*
			parsed = {
				menu_name: {
					title: 'full_url_path'
				}
			}
		*/

		$parsed = [];

		foreach ($this->menus as $name => $menu)
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
		$parsed_config = $this->parse_config();

		// Bail out early on invalid menu
		if ( ! $this->arr($parsed_config)->has_key($menu))
		{
			return '';
		}

		$menu_config = $parsed_config[$menu];

		foreach ($menu_config as $title => $path)
		{
			$selected = $this->string($path)->contains($this->path());
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