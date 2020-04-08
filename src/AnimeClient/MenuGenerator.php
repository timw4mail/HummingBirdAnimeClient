<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.3
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\Ion\{ArrayWrapper, StringWrapper};
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use Aura\Html\HelperLocator;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Exception\ConfigException;
use Psr\Http\Message\RequestInterface;

/**
 * Helper object to manage menu creation and selection
 */
final class MenuGenerator extends UrlGenerator {

	use ArrayWrapper;
	use StringWrapper;

	/**
	 * Html generation helper
	 *
	 * @var HelperLocator
	 */
	protected $helper;

	/**
	 * Request object
	 *
	 * @var RequestInterface
	 */
	protected $request;

	/**
	 * MenuGenerator constructor.
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerException
	 * @throws NotFoundException
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
			foreach ($menu['items'] as $pathName => $partialPath)
			{
				$title = (string)$this->string($pathName)->humanize()->titleize();
				$parsed[$name][$title] = (string)$this->string($menu['route_prefix'])->append($partialPath);
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

			$attrs = $selected
				? ['class' => 'selected']
				: [];

			$this->helper->ul()->rawItem($link, $attrs);
		}

		// Create the menu html
		return (string) $this->helper->ul();
	}
}
// End of MenuGenerator.php