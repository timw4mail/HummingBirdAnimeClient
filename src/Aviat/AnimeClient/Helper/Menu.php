<?php

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