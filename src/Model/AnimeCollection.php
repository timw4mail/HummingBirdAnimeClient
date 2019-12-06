<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
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
		$genres = $this->getGenreList();

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
		$id = $data['id'];

		// Check that the anime doesn't already exist
		$existing = $this->get($id);
		if ( ! empty($existing))
		{
			return;
		}

		$anime = (object)$this->animeModel->getAnimeById($id);
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

		$this->updateGenre($id);
	}

	/**
	 * Verify that an item was added
	 *
	 * @param $data
	 * @return bool
	 */
	public function wasAdded($data): bool
	{
		$row = $this->get($data['id']);

		return  ! empty($row);
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

		// Just in case, also update genres
		$this->updateGenre($id);
	}

	/**
	 * Verify that the collection item was updated
	 *
	 * @param $data
	 * @return bool
	 */
	public function wasUpdated($data): bool
	{
		$row = $this->get($data['hummingbird_id']);

		foreach ($data as $key => $value)
		{
			if ((string)$row[$key] !== (string)$value)
			{
				return FALSE;
			}
		}

		return TRUE;
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

	public function wasDeleted($data): bool
	{
		$animeRow = $this->get($data['hummingbird_id']);

		return empty($animeRow);
	}

	/**
	 * Get the details of a collection item
	 *
	 * @param int $kitsuId
	 * @return array | false
	 */
	public function get($kitsuId)
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
				}
				else
				{
					$output[$id] = [$genre];
				}
			}
		}
		catch (PDOException $e) {}

		$this->db->reset_query();

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
		// Get api information
		$anime = $this->animeModel->getAnimeById($animeId);

		$this->addNewGenres($anime['genres']);

		$genreInfo = $this->getGenreData();
		$genres = $genreInfo['genres'];
		$links = $genreInfo['links'];

		$linksToInsert = [];

		foreach ($anime['genres'] as $animeGenre)
		{
			// Update link table
			// Get id of genre to put in link table
			$flippedGenres = array_flip($genres);
			$genreId = $flippedGenres[$animeGenre];

			$animeLinks = $links[$animeId] ?? [];

			if ( ! \in_array($flippedGenres[$animeGenre], $animeLinks, TRUE))
			{
				$linksToInsert[] = [
					'hummingbird_id' => $animeId,
					'genre_id' => $genreId,
				];
			}
		}

		if ( ! empty($linksToInsert))
		{
			$this->db->insertBatch('genre_anime_set_link', $linksToInsert);
		}
	}

	/**
	 * Add genres to the database
	 *
	 * @param array $genres
	 */
	private function addNewGenres(array $genres): void
	{
		$existingGenres = $this->getExistingGenres();
		$newGenres = array_diff($genres, $existingGenres);

		$insert = [];

		foreach ($newGenres as $genre)
		{
			$insert[] = [
				'genre' => $genre,
			];
		}

		try
		{
			$this->db->insert_batch('genres', $insert);
		}
		catch (PDOException $e)
		{
			dump($e);
		}
	}

	/**
	 * Get list of existing genres
	 *
	 * @return array
	 */
	private function getGenreData(): array
	{
		return [
			'genres' => $this->getExistingGenres(),
			'links' => $this->getExistingGenreLinkEntries(),
		];
	}

	private function getExistingGenres(): array
	{
		$genres = [];

		// Get existing genres
		$query = $this->db->select('id, genre')
			->from('genres')
			->get();

		foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $genre)
		{
			$genres[$genre['id']] = $genre['genre'];
		}

		$this->db->reset_query();

		return $genres;
	}

	private function getExistingGenreLinkEntries(): array
	{
		$links = [];

		$query = $this->db->select('hummingbird_id, genre_id')
			->from('genre_anime_set_link')
			->get();

		foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $link)
		{
			if (array_key_exists($link['hummingbird_id'], $links))
			{
				$links[$link['hummingbird_id']][] = $link['genre_id'];
			} else
			{
				$links[$link['hummingbird_id']] = [$link['genre_id']];
			}
		}

		$this->db->reset_query();

		return $links;
	}
}
// End of AnimeCollectionModel.php