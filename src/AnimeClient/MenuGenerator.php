<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aura\Html\HelperLocator;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use Aviat\Ion\Exception\ConfigException;
use Aviat\Ion\Type\{ArrayType, StringType};
use Psr\Http\Message\ServerRequestInterface;

/**
 * Helper object to manage menu creation and selection
 */
final class MenuGenerator extends UrlGenerator
{
	/**
	 * Html generation helper
	 */
	protected HelperLocator $helper;

	/**
	 * Request object
	 */
	protected ServerRequestInterface $request;

	public static function new(ContainerInterface $container): self
	{
		return new self($container);
	}

	/**
	 * Generate the html structure of the menu selected
	 *
	 * @throws ConfigException
	 */
	public function generate(string $menu): string
	{
		$menus = $this->config->get('menus');
		$parsedConfig = $this->parseConfig($menus);

		// Bail out early on invalid menu
		if ( ! ArrayType::from($parsedConfig)->hasKey($menu))
		{
			return '';
		}

		$menuConfig = $parsedConfig[$menu];

		foreach ($menuConfig as $title => $path)
		{
			$has = StringType::from($this->path())->contains($path);
			$selected = ($has && mb_strlen($this->path()) >= mb_strlen($path));

			$linkAttrs = ($selected)
				? ['aria-current' => 'location']
				: [];
			$link = $this->helper->a($this->url($path), $title, $linkAttrs);

			$attrs = $selected
				? ['class' => 'selected']
				: [];

			$this->helper->ul()->rawItem($link, $attrs);
		}

		// Create the menu html
		return (string) $this->helper->ul();
	}

	/**
	 * MenuGenerator constructor.
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	private function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->helper = $container->get('html-helper');
		$this->request = $container->get('request');
	}

	/**
	 * Generate the full menu structure from the config files
	 *
	 * @return array<mixed, array<string, string>>
	 */
	private function parseConfig(array $menus): array
	{
		$parsed = [];

		foreach ($menus as $name => $menu)
		{
			$parsed[$name] = [];

			foreach ($menu['items'] as $pathName => $partialPath)
			{
				$title = (string) StringType::from($pathName)->humanize()->titleize();
				$parsed[$name][$title] = (string) StringType::from($menu['route_prefix'])->append($partialPath);
			}
		}

		return $parsed;
	}
}

// End of MenuGenerator.php
