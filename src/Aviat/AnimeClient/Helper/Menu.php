<?php

namespace Aviat\AnimeClient\Helper;

use Aviat\AnimeClient\MenuGenerator;

class Menu {

	use \Aviat\Ion\Di\ContainerAware;

	public function __invoke($menu_name)
	{
		$generator = new MenuGenerator($this->container);
		return $generator->generate($menu_name);
	}

}
// End of Menu.php