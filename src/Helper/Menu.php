<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 5.6
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Helper;

use Aviat\AnimeClient\MenuGenerator;

/**
 * MenuGenerator helper wrapper
 */
class Menu {

	use \Aviat\Ion\Di\ContainerAware;

	/**
	 * Create the html for the selected menu
	 *
	 * @param  string $menu_name
	 * @return string
	 */
	public function __invoke($menu_name)
	{
		$generator = new MenuGenerator($this->container);
		return $generator->generate($menu_name);
	}

}
// End of Menu.php