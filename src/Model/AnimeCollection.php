<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.1
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\Ion\Di\ContainerInterface;
use PDO;
use PDOException;

/**
 * Model for getting anime collection data
 */
final class AnimeCollection extends Collection {

	/**
	 * Anime API Model
	 * @var Anime $animeModel
	 */
	protected $animeModel;

	/**
	 * Create the collection model
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->animeModel = $container->get('anime-model');
	}

	/**
	 * Get collection from the database, and organize by media type
	 *
	 * @return array
	 */
	public function getCollection(): array
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
	public function getMediaTypeList(): array
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
	 * @param string $id
	 * @return array
	 */
	public function getCollectionEntry($id): array
	{
		$query = $this->db->from('anime_set')
			->where('hummingbird_id', $id)
			->get();

		return $query->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Get full collection from the database
	 *
	 * @return array
	 */
	private function getCollectionFromDatabase(): array
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
			->group_by('a.hummingbird_id, media.type')
			->get();

		// Add genres associated with each item
		$rows = $query->fetchAll(PDO::FETCH_ASSOC);
		$genres = $this->getGenresForList();

		foreach($rows as &$row)
		{
			$id = $row['hummingbird_id'];

			$row['genres'] = array_key_exists($id, $genres)
				? $genres[$id]
				: [];

			sort($row['genres']);
		}

		return $rows;
	}

	/**
	 * Add an item to the anime collection
	 *
	 * @param array $data
	 * @return void
	 */
	public function add($data): void
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
	public function update($data): void
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
	public function delete($data): void
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
	public function get($kitsuId): array
	{
		$query = $this->db->from('anime_set')
			->where('hummingbird_id', $kitsuId)
			->get();

		return $query->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Get genres for anime collection items
	 *
	 * @param array $filter
	 * @return array
	 */
	public function getGenreList(array $filter = []): array
	{
		$output = [];

		// Catch the missing table PDOException
		// so that the collection does not show an
		// error by default
		try
		{
			$this->db->select('hummingbird_id, genre')
				->from('genre_anime_set_link gl')
				->join('genres g', 'g.id=gl.genre_id', 'left');


			if ( ! empty($filter))
			{
				$this->db->whereIn('hummingbird_id', $filter);
			}

			$query = $this->db->orderBy('hummingbird_id')
				->orderBy('genre')
				->get();

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
					$output[$id][] = $genre;
				} else
				{
					$output[$id] = [$genre];
				}
			}
		}
		catch (PDOException $e) {}

		return $output;
	}

	/**
	 * Get the list of genres from the database
	 *
	 * @return array
	 */
	private function getGenresForList(): array
	{
		$query = $this->db->select('hummingbird_id, genre')
			->from('genres g')
			->join('genre_anime_set_link gasl', 'gasl.genre_id=g.id')
			->get();

		$rows = $query->fetchAll(PDO::FETCH_ASSOC);
		$output = [];

		foreach($rows as $row)
		{
			$output[$row['hummingbird_id']][] = $row['genre'];
		}

		return $output;
	}

	/**
	 * Update genre information for selected anime
	 *
	 * @param string $animeId The current anime
	 * @return void
	 */
	private function updateGenre($animeId): void
	{
		$genreInfo = $this->getGenreData();
		$genres = $genreInfo['genres'];
		$links = $genreInfo['links'];

		// Get api information
		$anime = $this->animeModel->getAnimeById($animeId);

		foreach ($anime['genres'] as $genre)
		{
			// Add genres that don't currently exist
			if ( ! \in_array($genre, $genres, TRUE))
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
				if ( ! \in_array($flippedGenres[$genre], $links[$animeId], TRUE))
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
	private function getGenreData(): array
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