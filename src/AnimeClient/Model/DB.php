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

namespace Aviat\AnimeClient\Model;

use Aviat\Ion\Di\{ContainerAware, ContainerInterface};

/**
 * Base model for database interaction
 */
abstract class DB
{
	use ContainerAware;

	/**
	 * The database connection information array
	 */
	protected array $dbConfig = [];

	/**
	 * Constructor
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->dbConfig = $container->get('config')->get('database');
		$this->setContainer($container);
	}
}

// End of DB.php
