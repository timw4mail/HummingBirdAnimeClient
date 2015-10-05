<?php

namespace Aviat\AnimeClient;

use Aviat\Ion\Di\ContainerInterface;

/**
 * Helper object to manage menu creation and selection
 */
class MenuGenerator extends RoutingBase {

	use \Aviat\Ion\Di\ContainerAware;
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
	 * Create menu generator
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->menus = $this->config->menus;
		$this->helper = $container->get('html-helper');
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
			$parsed = [
				'menu_name' => [
					'items' => [
						'title' => 'full_url_path',
					],
					'children' => [
						'title' => 'full_url_path'
					]
				]
			]
		*/

		$parsed = [];

		foreach($this->menus as $name => $menu)
		{
			$parsed[$name] = [];
			foreach($menu['items'] as $path_name => $partial_path)
			{
				$title = $this->string($path_name)->humanize()->titleize();
				$parsed[$name]['items'][$title] = $this->string($menu['route_prefix'])->append($partial_path);
			}

			// @TODO: Handle child menu(s)
			if (count($menu['children']) > 0)
			{

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
		$parsed_config =  $this->parse_config();
		$menu_config = $parsed_config[$menu];

		// Array of list items to add to the main menu
		$main_menu = [];


		// Start the menu list
		$helper->ul();


	}
}
// End of MenuGenerator.php