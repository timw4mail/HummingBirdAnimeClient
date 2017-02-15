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

use Aviat\AnimeClient\Model\DB;
use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use PDO;
use PDOException;

/**
 * Base model for anime and manga collections
 */
class Collection extends DB {
	
	use ContainerAware;

	/**
	 * Anime API Model
	 * @var object $animeModel
	 */
	protected $animeModel;

	/**
	 * Whether the database is valid for querying
	 * @var boolean
	 */
	protected $validDatabase = FALSE;

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
			$this->db = \Query($this->dbConfig['collection']);
		}
		catch (PDOException $e)
		{
			//$this->validDatabase = FALSE;
			//return FALSE;
		}
		$this->animeModel = $container->get('anime-model');

		// Is database valid? If not, set a flag so the
		// app can be run without a valid database
		if ($this->dbConfig['collection']['type'] === 'sqlite')
		{
			$dbFileName = $this->dbConfig['collection']['file'];

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
		else
		{
			$this->validDatabase = TRUE;
		}
	}

	/**
	 * Get genres for anime collection items
	 *
	 * @param array $filter
	 * @return array
	 */
	public function getGenreList($filter = [])
	{
		$this->db->select('hummingbird_id, genre')
			->from('genre_anime_set_link gl')
			->join('genres g', 'g.id=gl.genre_id', 'left');


		if ( ! empty($filter))
		{
			$this->db->where_in('hummingbird_id', $filter);
		}

		$query = $this->db->order_by('hummingbird_id')
			->order_by('genre')
			->get();

		$output = [];

		foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			$id = $row['hummingbird_id'];
			$genre = $row['genre'];

			// Empty genre names aren't useful
			if (empty($genre))
			{
				continue;
			}

			if (array_key_exists($id, $output))
			{
				array_push($output[$id], $genre);
			}
			else
			{
				$output[$id] = [$genre];
			}
		}

		return $output;
	}

}
// End of Collection.php