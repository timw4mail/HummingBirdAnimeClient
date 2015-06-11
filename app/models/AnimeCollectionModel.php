<?php

/**
 * Model for getting anime collection data
 */
class AnimeCollectionModel extends BaseModel {
	protected $base_url = "";
	private $db;
	private $anime_model;
	private $db_config;

	public function __construct()
	{
		$this->db_config = require_once(__DIR__ . '/../config/database.php');
		$this->db = Query($this->db_config['collection']);

		$this->anime_model = new AnimeModel();

		parent::__construct();

		// Do an import if an import file exists
		$this->json_import();
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

		foreach($raw_collection as $row)
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
	 * Get full collection from the database
	 *
	 * @return array
	 */
	private function _get_collection()
	{
		$query = $this->db->select('hummingbird_id, slug, title, alternate_title, show_type, age_rating, episode_count, episode_length, cover_image, notes, media.type as media')
			->from('anime_set a')
			->join('media', 'media.id=a.media_id', 'inner')
			->order_by('media')
			->order_by('title')
			->get();

		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Import anime into collection from a json file
	 *
	 * @return void
	 */
	private function json_import()
	{
		if ( ! file_exists('import.json')) return;

		$anime = json_decode(file_get_contents("import.json"));

		foreach($anime as $item)
		{
			$this->db->set([
				'hummingbird_id' => $item->id,
				'slug' => $item->slug,
				'title' => $item->title,
				'alternate_title' => $item->alternate_title,
				'show_type' => $item->show_type,
				'age_rating' => $item->age_rating,
				'cover_image' => $this->get_cached_image($item->cover_image, $item->slug, 'anime'),
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
	 * Update genre information
	 *
	 * @return void
	 */
	private function update_genres()
	{
		$genres = [];
		$flipped_genres = [];

		$links = [];

		// Get existing genres
		$query = $this->db->select('id, genre')
			->from('genres')
			->get();
		foreach($query->fetchAll(PDO::FETCH_ASSOC) as $genre)
		{
			$genres[$genre['id']] = $genre['genre'];
		}

		// Get existing link table entries
		$query = $this->db->select('hummingbird_id, genre_id')
			->from('genre_anime_set_link')
			->get();
		foreach($query->fetchAll(PDO::FETCH_ASSOC) as $link)
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

		// Get the anime collection
		$collection = $this->_get_collection();
		foreach($collection as $anime)
		{
			// Get api information
			$api = $this->anime_model->get_anime($anime['hummingbird_id']);


			foreach($api['genres'] as $genre)
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
					'hummingbird_id' => $anime['hummingbird_id'],
					'genre_id' => $flipped_genres[$genre['name']]
				];

				if (array_key_exists($anime['hummingbird_id'], $links))
				{
					if ( ! in_array($flipped_genres[$genre['name']], $links[$anime['hummingbird_id']]))
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
	}
}
// End of AnimeCollectionModel.php