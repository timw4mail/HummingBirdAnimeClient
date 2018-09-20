<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\Ion\{
	ArrayWrapper, StringWrapper
};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Exception\ConfigException;

/**
 * Helper object to manage menu creation and selection
 */
final class MenuGenerator extends UrlGenerator {

	use ArrayWrapper;
	use StringWrapper;

	/**
	 * Html generation helper
	 *
	 * @var \Aura\Html\HelperLocator
	 */
	protected $helper;

	/**
	 * Request object
	 *
	 * @var \Psr\Http\Message\RequestInterface
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
	protected function parseConfig(array $menus) : array
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
	 * @throws ConfigException
	 * @return string
	 */
	public function generate($menu) : string
	{
		$menus = $this->config->get('menus');
		$parsedConfig = $this->parseConfig($menus);

		// Bail out early on invalid menu
		if ( ! $this->arr($parsedConfig)->hasKey($menu))
		{
			return '';
		}

		$menuConfig = $parsedConfig[$menu];

		foreach ($menuConfig as $title => $path)
		{
			$has = $this->string($this->path())->contains($path);
			$selected = ($has && mb_strlen($this->path()) >= mb_strlen($path));

			$link = $this->helper->a($this->url($path), $title);

			$attrs = ($selected)
				? ['class' => 'selected']
				: [];

			$this->helper->ul()->rawItem($link, $attrs);
		}

		// Create the menu html
		return (string) $this->helper->ul();
	}
}
// End of MenuGenerator.php