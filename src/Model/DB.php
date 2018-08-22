<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Aviat\Ion\{ArrayWrapper, StringWrapper};

/**
 * Base model for database interaction
 */
class DB {
	use ArrayWrapper;
	use ContainerAware;
	use StringWrapper;

	/**
	 * The query builder object
	 * @var \Query\Query_Builder_Interface
	 */
	protected $db;

	/**
	 * The database connection information array
	 * @var array $dbConfig
	 */
	protected $dbConfig = [];

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->dbConfig = $container->get('config')->get('database');
		$this->setContainer($container);
	}
}
// End of DB.php