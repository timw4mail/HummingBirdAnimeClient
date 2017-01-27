<?php declare(strict_types=1);
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\Ion\
{
	ArrayWrapper, StringWrapper
};
use Aviat\Ion\Di\ContainerInterface;

/**
 * Helper object to manage menu creation and selection
 */
class MenuGenerator extends UrlGenerator {

	use ArrayWrapper;
	use StringWrapper;

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
		if ( ! $this->arr($parsed_config)->hasKey($menu))
		{
			return '';
		}

		$menu_config = $parsed_config[$menu];

		foreach ($menu_config as $title => $path)
		{
			$has = $this->string($this->path())->contains($path);
			$selected = ($has && strlen($this->path()) >= strlen($path));

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