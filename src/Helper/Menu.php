<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
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
	 * @param  string $menuName
	 * @return string
	 */
	public function __invoke($menuName)
	{
		$generator = new MenuGenerator($this->container);
		return $generator->generate($menuName);
	}

}
// End of Menu.php