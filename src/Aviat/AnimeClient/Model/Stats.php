<?php
/**
 * Anime API Model
 */

namespace Aviat\AnimeClient\Model;

use Avait\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Model\DB;

/**
 * Base Model for stats about lists and collection(s)
 */
class Stats extends DB {

	use StatsChartsTrait;

	/**
	 * Constructor
	 *
	 * @param Container $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->chartSetup();
	}

}
// End of Stats.php