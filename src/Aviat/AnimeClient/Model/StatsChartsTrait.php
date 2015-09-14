<?php

namespace Aviat\AnimeClient\Model;

use CpChart\Services\pChartFactory;

/**
 * Trait for chart generation
 */
trait StatsChartsTrait {


	/**
	 * @var pChartFactory
	 */
	protected $pchart;

	/**
	 * Initial setup method
	 *
	 * @return void
	 */
	protected function chartSetup()
	{
		$this->pchart = new pChartFactory();
	}

}
// End of StatsChartsTrait.php