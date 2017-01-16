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


use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Model\DB;
use PDO;
use PDOException;

/**
 * Base model for anime and manga collections
 */
class Collection extends DB {
	
	use ContainerAware;

	/**
	 * Anime API Model
	 * @var object $anime_model
	 */
	protected $anime_model;

	/**
	 * Whether the database is valid for querying
	 * @var boolean
	 */
	protected $valid_database = FALSE;

	/**
	 * Create a new collection object
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		
		parent::__construct($container->get('config'));

		try
		{
			$this->db = \Query($this->db_config['collection']);
		}
		catch (PDOException $e)
		{
			//$this->valid_database = FALSE;
			//return FALSE;
		}
		$this->anime_model = $container->get('anime-model');

		// Is database valid? If not, set a flag so the
		// app can be run without a valid database
		if ($this->db_config['collection']['type'] === 'sqlite')
		{
			$db_file_name = $this->db_config['collection']['file'];

			if ($db_file_name !== ':memory:' && file_exists($db_file_name))
			{
				$db_file = file_get_contents($db_file_name);
				$this->valid_database = (strpos($db_file, 'SQLite format 3') === 0);
			}
			else
			{
				$this->valid_database = FALSE;
			}
		}
		else
		{
			$this->valid_database = TRUE;
		}
	}

	/**
	 * Get genres for anime collection items
	 *
	 * @param array $filter
	 * @return array
	 */
	public function get_genre_list($filter = [])
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