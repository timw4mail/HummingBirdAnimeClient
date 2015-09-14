<?php
/**
 * Anime API Model
 */

namespace AnimeClient\Model;

use AnimeClient\Base\Model\DB;
use AnimeClient\Base\Container;

use StatsChartsTrait;

class Stats extends DB {

	/**
	 * Constructor
	 *
	 * @param Config $config
	 */
	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->chartSetup();
	}

}
// End of Stats.php