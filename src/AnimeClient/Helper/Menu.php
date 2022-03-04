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

namespace Aviat\AnimeClient\Helper;

use Aviat\AnimeClient\MenuGenerator;
use Aviat\Ion\Di\ContainerAware;

/**
 * MenuGenerator helper wrapper
 */
final class Menu
{
	use ContainerAware;

	/**
	 * Create the html for the selected menu
	 *
	 * @return string
	 */
	public function __invoke(string $menuName)
	{
		return MenuGenerator::new($this->container)->generate($menuName);
	}
}

// End of Menu.php
