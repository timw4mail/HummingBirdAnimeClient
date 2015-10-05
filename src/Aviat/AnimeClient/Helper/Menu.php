<?php

namespace Aviat\AnimeClient\Helper;

use Aura\Html\Helper\AbstractHelper;

use Aviat\AnimeClient\MenuGenerator;

class Menu extends AbstractHelper {

	use \Aviat\Ion\Di\ContainerAware;

	public function __invoke($menu_name)
	{
		$generator = new MenuGenerator($this->container);
		return $generator->generate($menu_name);
	}

}
// End of Menu.php