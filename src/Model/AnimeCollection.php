<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
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
	public function getCollection()
	{
		$rawCollection = $this->getCollectionFromDatabase();

		$collection = [];

		foreach ($rawCollection as $row)
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
	public function getMediaTypeList()
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
	public function getCollectionEntry($id)
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
	private function getCollectionFromDatabase()
	{
		if ( ! $this->validDatabase)
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
		$anime = (object)$this->animeModel->getAnimeById($data['id']);
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

		$this->updateGenre($data['id']);
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
	 * @param int $kitsuId
	 * @return array
	 */
	public function get($kitsuId)
	{
		$query = $this->db->from('anime_set')
			->where('hummingbird_id', $kitsuId)
			->get();

		return $query->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Update genre information for selected anime
	 *
	 * @param int $animeId The current anime
	 * @return void
	 */
	private function updateGenre($animeId)
	{
		$genreInfo = $this->getGenreData();
		extract($genreInfo);

		// Get api information
		$anime = $this->animeModel->getAnimeById($animeId);

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
			$flippedGenres = array_flip($genres);

			$insertArray = [
				'hummingbird_id' => $animeId,
				'genre_id' => $flippedGenres[$genre]
			];

			if (array_key_exists($animeId, $links))
			{
				if ( ! in_array($flippedGenres[$genre], $links[$animeId]))
				{
					$this->db->set($insertArray)->insert('genre_anime_set_link');
				}
			}
			else
			{
				$this->db->set($insertArray)->insert('genre_anime_set_link');
			}
		}
	}

	/**
	 * Get list of existing genres
	 *
	 * @return array
	 */
	private function getGenreData()
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