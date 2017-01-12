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

namespace Aviat\AnimeClient\Model;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Model;

/**
 * Base model for database interaction
 */
class DB extends Model {
	use \Aviat\Ion\Di\ContainerAware;

	/**
	 * The query builder object
	 * @var object $db
	 */
	protected $db;

	/**
	 * The database connection information array
	 * @var array $db_config
	 */
	protected $db_config;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->db_config = $container->get('config')->get('database');
		$this->setContainer($container);
	}
}
// End of DB.php