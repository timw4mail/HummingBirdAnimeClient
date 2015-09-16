<?php
/**
 * Anime API Model
 */

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\Model\DB;
use Aviat\AnimeClient\Container;

use StatsChartsTrait;

class Stats extends DB {

	/**
	 * Constructor
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->chartSetup();
	}

}
// End of Stats.php