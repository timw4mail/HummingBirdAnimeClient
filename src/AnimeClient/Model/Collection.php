<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\Ion\Di\ContainerInterface;
use PDOException;

use Query\Query_Builder_Interface;
use function Query;

/**
 * Base model for anime and manga collections
 */
class Collection extends DB {

	/**
	 * The query builder object
	 * @var Query_Builder_Interface
	 */
	protected Query_Builder_Interface $db;

	/**
	 * Whether the database is valid for querying
	 * @var boolean
	 */
	protected bool $validDatabase = FALSE;

	/**
	 * Create a new collection object
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		try
		{
			$this->db = Query($this->dbConfig);
			$this->validDatabase = TRUE;
		}
		catch (PDOException $e)
		{
			$this->db = Query([
				'type' => 'sqlite',
				'file' => ':memory:',
			]);
		}

		// Is database valid? If not, set a flag so the
		// app can be run without a valid database
		if ($this->dbConfig['type'] === 'sqlite')
		{
			$dbFileName = $this->dbConfig['file'];

			if ($dbFileName !== ':memory:' && file_exists($dbFileName))
			{
				$dbFile = file_get_contents($dbFileName);
				$this->validDatabase = (strpos($dbFile, 'SQLite format 3') === 0);
			}
			else
			{
				$this->validDatabase = FALSE;
			}
		}
		else if ($this->db === NULL)
		{
			$this->validDatabase = FALSE;
		}
	}
}
// End of Collection.php