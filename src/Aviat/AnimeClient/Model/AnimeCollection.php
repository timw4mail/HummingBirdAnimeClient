<?php
/**
 * Anime Collection DB Model
 */

namespace Aviat\AnimeClient\Model;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Model\Anime as AnimeModel;

/**
 * Model for getting anime collection data
 */
class AnimeCollection extends DB {

	/**
	 * Anime API Model
	 * @var object $anime_model
	 */
	private $anime_model;

	/**
	 * Whether the database is valid for querying
	 * @var bool
	 */
	private $valid_database = FALSE;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		try
		{
			$this->db = \Query($this->db_config['collection']);
		}
		catch (\PDOException $e)
		{
			$this->valid_database = FALSE;
			return FALSE;
		}
		$this->anime_model = new AnimeModel($container);

		// Is database valid? If not, set a flag so the
		// app can be run without a valid database
		$db_file_name = $this->db_config['collection']['file'];
		if ($db_file_name !== ':memory:')
		{
			$db_file = @file_get_contents($db_file_name);
			$this->valid_database = (strpos($db_file, 'SQLite format 3') === 0);
		}
		else
		{
			$this->valid_database = TRUE;
		}

		// Do an import if an import file exists
		$this->json_import();
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


		if ( ! empty($filter)) $this->db->where_in('hummingbird_id', $filter);

		$query = $this->db->order_by('hummingbird_id')
			->order_by('genre')
			->get();

		$output = [];

		foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $row)
		{
			$id = $row['hummingbird_id'];
			$genre = $row['genre'];

			// Empty genre names aren't useful
			if (empty($genre)) continue;


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

	/**
	 * Get collection from the database, and organize by media type
	 *
	 * @return array
	 */
	public function get_collection()
	{
		$raw_collection = $this->_get_collection();

		$collection = [];

		foreach ($raw_collection as $row)
		{
			if (array_key_exists($row['media'], $collection))
			{
				$collection[$row['media']][] = $row;
			}
			else
			{
				$collection[$row['media']] = [$row];
			}
		}

		return $collection;
	}

	/**
	 * Get list of media types
	 *
	 * @return array
	 */
	public function get_media_type_list()
	{
		$output = array();

		$query = $this->db->select('id, type')
			->from('media')
			->get();

		foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $row)
		{
			$output[$row['id']] = $row['type'];
		}

		return $output;
	}

	/**
	 * Get item from collection for editing
	 *
	 * @param int $id
	 * @return array
	 */
	public function get_collection_entry($id)
	{
		$query = $this->db->from('anime_set')
			->where('hummingbird_id', (int)$id)
			->get();

		return $query->fetch(\PDO::FETCH_ASSOC);
	}

	/**
	 * Get full collection from the database
	 *
	 * @return array
	 */
	private function _get_collection()
	{
		if ( ! $this->valid_database) return [];

		$query = $this->db->select('hummingbird_id, slug, title, alternate_title, show_type,
			 age_rating, episode_count, episode_length, cover_image, notes, media.type as media')
			->from('anime_set a')
			->join('media', 'media.id=a.media_id', 'inner')
			->order_by('media')
			->order_by('title')
			->get();

		return $query->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Add an item to the anime collection
	 *
	 * @param array $data
	 * @return void
	 */
	public function add($data)
	{
		$anime = (object)$this->anime_model->get_anime($data['id']);

		$this->db->set([
			'hummingbird_id' => $data['id'],
			'slug' => $anime->slug,
			'title' => $anime->title,
			'alternate_title' => $anime->alternate_title,
			'show_type' => $anime->show_type,
			'age_rating' => $anime->age_rating,
			'cover_image' => basename(
				$this->get_cached_image($anime->cover_image, $anime->slug, 'anime')
			),
			'episode_count' => $anime->episode_count,
			'episode_length' => $anime->episode_length,
			'media_id' => $data['media_id'],
			'notes' => $data['notes']
		])->insert('anime_set');

		$this->update_genre($data['id']);
	}

	/**
	 * Update a collection item
	 *
	 * @param array $data
	 * @return void
	 */
	public function update($data)
	{
		// If there's no id to update, don't update
		if ( ! array_key_exists('hummingbird_id', $data)) return;

		$id = $data['hummingbird_id'];
		unset($data['hummingbird_id']);

		$this->db->set($data)
			->where('hummingbird_id', $id)
			->update('anime_set');
	}

	/**
	 * Get the details of a collection item
	 *
	 * @param int $hummingbird_id
	 * @return array
	 */
	public function get($hummingbird_id)
	{
		$query = $this->db->from('anime_set')
			->where('hummingbird_id', $hummingbird_id)
			->get();

		return $query->fetch(\PDO::FETCH_ASSOC);
	}

	/**
	 * Import anime into collection from a json file
	 *
	 * @return void
	 */
	private function json_import()
	{
		if ( ! file_exists('import.json')) return;
		if ( ! $this->valid_database) return;

		$anime = json_decode(file_get_contents("import.json"));

		foreach ($anime as $item)
		{
			$this->db->set([
				'hummingbird_id' => $item->id,
				'slug' => $item->slug,
				'title' => $item->title,
				'alternate_title' => $item->alternate_title,
				'show_type' => $item->show_type,
				'age_rating' => $item->age_rating,
				'cover_image' => basename(
					$this->get_cached_image($item->cover_image, $item->slug, 'anime')
				),
				'episode_count' => $item->episode_count,
				'episode_length' => $item->episode_length
			])->insert('anime_set');
		}

		// Delete the import file
		unlink('import.json');

		// Update genre info
		$this->update_genres();
	}

	/**
	 * Update genre information for selected anime
	 *
	 * @param int $anime_id The current anime
	 * @return void
	 */
	private function update_genre($anime_id)
	{
		$genre_info = $this->get_genre_data();
		extract($genre_info);

		// Get api information
		$anime = $this->anime_model->get_anime($anime_id);

		foreach ($anime['genres'] as $genre)
		{
			// Add genres that don't currently exist
			if ( ! in_array($genre['name'], $genres))
			{
				$this->db->set('genre', $genre['name'])
					->insert('genres');

				$genres[] = $genre['name'];
			}

			// Update link table
			// Get id of genre to put in link table
			$flipped_genres = array_flip($genres);

			$insert_array = [
				'hummingbird_id' => $anime['id'],
				'genre_id' => $flipped_genres[$genre['name']]
			];

			if (array_key_exists($anime['id'], $links))
			{
				if ( ! in_array($flipped_genres[$genre['name']], $links[$anime['id']]))
				{
					$this->db->set($insert_array)->insert('genre_anime_set_link');
				}
			}
			else
			{
				$this->db->set($insert_array)->insert('genre_anime_set_link');
			}
		}
	}

	/**
	 * Get list of existing genres
	 *
	 * @return array
	 */
	private function get_genre_data()
	{
		$genres = [];
		$links = [];

		// Get existing genres
		$query = $this->db->select('id, genre')
			->from('genres')
			->get();
		foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $genre)
		{
			$genres[$genre['id']] = $genre['genre'];
		}

		// Get existing link table entries
		$query = $this->db->select('hummingbird_id, genre_id')
			->from('genre_anime_set_link')
			->get();
		foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $link)
		{
			if (array_key_exists($link['hummingbird_id'], $links))
			{
				$links[$link['hummingbird_id']][] = $link['genre_id'];
			}
			else
			{
				$links[$link['hummingbird_id']] = [$link['genre_id']];
			}
		}

		return [
			'genres' => $genres,
			'links' => $links
		];
	}

	/**
	 * Update genre information for the entire collection
	 *
	 * @return void
	 */
	private function update_genres()
	{
		// Get the anime collection
		$collection = $this->_get_collection();
		foreach ($collection as $anime)
		{
			// Get api information
			$this->update_genre($anime['hummingbird_id']);
		}
	}
}
// End of AnimeCollectionModel.php