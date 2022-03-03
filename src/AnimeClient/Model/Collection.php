<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\Ion\Di\ContainerInterface;
use PDOException;

use Query\QueryBuilderInterface;
use function Query;

/**
 * Base model for anime and manga collections
 */
class Collection extends DB {

	/**
	 * The query builder object
	 */
	protected ?QueryBuilderInterface $db;

	/**
	 * Create a new collection object
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		try
		{
			$this->db = Query($this->dbConfig);
		}
		catch (PDOException)
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

			if ($dbFileName !== ':memory:')
			{
				$rawFile = file_get_contents($dbFileName);
				$dbFile = ($rawFile !== FALSE) ? $rawFile : '';
				$this->db = (str_starts_with($dbFile, 'SQLite format 3')) ? $this->db : NULL;
			}
		}
	}
}

// End of Collection.php