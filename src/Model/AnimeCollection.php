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

use Aviat\AnimeClient\API\Kitsu;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;
use PDO;

/**
 * Model for getting anime collection data
 */
class AnimeCollection extends Collection {

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
		$output = [];

		$query = $this->db->select('id, type')
			->from('media')
			->get();

		foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row)
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

		return $query->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Get full collection from the database
	 *
	 * @return array
	 */
	private function _get_collection()
	{
		if ( ! $this->valid_database)
		{
			return [];
		}

		$query = $this->db->select('hummingbird_id, slug, title, alternate_title, show_type,
			 age_rating, episode_count, episode_length, cover_image, notes, media.type as media')
			->from('anime_set a')
			->join('media', 'media.id=a.media_id', 'inner')
			->order_by('media')
			->order_by('title')
			->get();

		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Add an item to the anime collection
	 *
	 * @param array $data
	 * @return void
	 */
	public function add($data)
	{
		$anime = (object)$this->anime_model->getAnimeById($data['id']);
		$this->db->set([
			'hummingbird_id' => $data['id'],
			'slug' => $anime->slug,
			'title' => array_shift($anime->titles),
			'alternate_title' => implode('<br />', $anime->titles),
			'show_type' => $anime->show_type,
			'age_rating' => $anime->age_rating,
			'cover_image' => $anime->cover_image,
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
		if ( ! array_key_exists('hummingbird_id', $data))
		{
			return;
		}

		$id = $data['hummingbird_id'];
		unset($data['hummingbird_id']);

		$this->db->set($data)
			->where('hummingbird_id', $id)
			->update('anime_set');
	}

	/**
	 * Remove a collection item
	 *
	 * @param  array $data
	 * @return void
	 */
	public function delete($data)
	{
		// If there's no id to update, don't delete
		if ( ! array_key_exists('hummingbird_id', $data))
		{
			return;
		}

		$this->db->where('hummingbird_id', $data['hummingbird_id'])
			->delete('genre_anime_set_link');

		$this->db->where('hummingbird_id', $data['hummingbird_id'])
			->delete('anime_set');
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

		return $query->fetch(PDO::FETCH_ASSOC);
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
		$anime = $this->anime_model->getAnimeById($anime_id);

		foreach ($anime['genres'] as $genre)
		{
			// Add genres that don't currently exist
			if ( ! in_array($genre, $genres))
			{
				$this->db->set('genre', $genre)
					->insert('genres');

				$genres[] = $genre;
			}

			// Update link table
			// Get id of genre to put in link table
			$flipped_genres = array_flip($genres);

			$insert_array = [
				'hummingbird_id' => $anime_id,
				'genre_id' => $flipped_genres[$genre]
			];

			if (array_key_exists($anime_id, $links))
			{
				if ( ! in_array($flipped_genres[$genre], $links[$anime_id]))
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
		foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $genre)
		{
			$genres[$genre['id']] = $genre['genre'];
		}

		// Get existing link table entries
		$query = $this->db->select('hummingbird_id, genre_id')
			->from('genre_anime_set_link')
			->get();
		foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $link)
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
}
// End of AnimeCollectionModel.php