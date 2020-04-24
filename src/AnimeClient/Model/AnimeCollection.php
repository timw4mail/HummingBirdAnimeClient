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
use PDO;
use PDOException;
use function in_array;

/**
 * Model for getting anime collection data
 */
final class AnimeCollection extends Collection {

	/**
	 * Anime API Model
	 * @var Anime $animeModel
	 */
	protected Anime $animeModel;

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
		if ($this->validDatabase === FALSE)
		{
			return [];
		}

		$flatList = [];

		$query = $this->db->select('id, type')
			->from('media')
			->get();

		foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			$flatList[$row['id']] = $row['type'];
		}

		// Organize the media types into groups
		return [
			'Common' => [
				2 => $flatList[2], // Blu-ray
				3 => $flatList[3], // DVD
				7 => $flatList[7], // Digital
				4 => $flatList[4], // Bootleg
			],
			'Retro' => [
				5 => $flatList[5], // LaserDisc
				6 => $flatList[6], // VHS
				9 => $flatList[9], // Betamax
				8 => $flatList[8], // Video CD
			],
			'Other' => [
				10 => $flatList[10], // UMD
				11 => $flatList[11], // Other
			]
		];
	}

	/**
	 * Add an item to the anime collection
	 *
	 * @param array $data
	 * @return void
	 */
	public function add($data): void
	{
		if ($this->validDatabase === FALSE)
		{
			return;
		}

		// Check that the anime doesn't already exist
		if ($this->has($data['id']))
		{
			return;
		}

		$id = $data['id'];
		$anime = (object)$this->animeModel->getAnimeById($id);

		$this->db->beginTransaction();
		$this->db->set([
			'hummingbird_id' => $id,
			'slug' => $anime->slug,
			'title' => array_shift($anime->titles),
			'alternate_title' => implode('<br />', $anime->titles),
			'show_type' => $anime->show_type,
			'age_rating' => $anime->age_rating,
			'cover_image' => $anime->cover_image,
			'episode_count' => $anime->episode_count,
			'episode_length' => $anime->episode_length,
			'notes' => $data['notes']
		])->insert('anime_set');

		$this->updateMediaLink($id, $data['media_id']);

		$this->updateGenres($id);

		$this->db->commit();
	}

	/**
	 * Verify that an item was added
	 *
	 * @param array|null|object $data
	 * @return bool
	 */
	public function wasAdded($data): bool
	{
		if ($this->validDatabase === FALSE)
		{
			return FALSE;
		}

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
		if ($this->validDatabase === FALSE)
		{
			return;
		}

		// If there's no id to update, don't update
		if ( ! array_key_exists('hummingbird_id', $data))
		{
			return;
		}

		$id = $data['hummingbird_id'];
		$media = $data['media_id'];
		unset($data['hummingbird_id'], $data['media_id']);

		$this->db->beginTransaction();

		// If updating from the 'add' page, there
		// might be no data to actually update in
		// the anime_set table
		if ( ! empty($data))
		{
			$this->db->set($data)
				->where('hummingbird_id', $id)
				->update('anime_set');
		}

		// Update media and genres
		$this->updateMediaLink($id, $media);
		$this->updateGenres($id);

		$this->db->commit();
	}

	/**
	 * Verify that the collection item was updated
	 *
	 * @param array|null|object $data
	 *
	 * @return bool
	 */
	public function wasUpdated($data): bool
	{
		if ($this->validDatabase === FALSE)
		{
			return FALSE;
		}

		$row = $this->get($data['hummingbird_id']);

		foreach ($data as $key => $value)
		{
			if (is_array($row[$key]))
			{
				if ($row[$key] === $value)
				{
					continue;
				}

				return FALSE;
			}

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
		if ($this->validDatabase === FALSE)
		{
			return;
		}

		// If there's no id to update, don't delete
		if ( ! array_key_exists('hummingbird_id', $data))
		{
			return;
		}

		$this->db->beginTransaction();

		$this->db->where('hummingbird_id', $data['hummingbird_id'])
			->delete('genre_anime_set_link');

		$this->db->where('hummingbird_id', $data['hummingbird_id'])
			->delete('anime_set_media_link');

		$this->db->where('hummingbird_id', $data['hummingbird_id'])
			->delete('anime_set');

		$this->db->commit();
	}

	/**
	 * @param array|null|object $data
	 * @return bool
	 */
	public function wasDeleted($data): bool
	{
		if ($this->validDatabase === FALSE)
		{
			return FALSE;
		}

		return $this->has($data['hummingbird_id']) === FALSE;
	}

	/**
	 * Get the details of a collection item
	 *
	 * @param int|string $kitsuId
	 * @return array
	 */
	public function get($kitsuId): array
	{
		if ($this->validDatabase === FALSE)
		{
			return [];
		}

		// Get the main row data
		$row = $this->db->from('anime_set')
			->where('hummingbird_id', $kitsuId)
			->get()
			->fetch(PDO::FETCH_ASSOC);

		// Get the media ids
		$mediaRows = $this->db->select('media_id')
			->from('anime_set_media_link')
			->where('hummingbird_id', $kitsuId)
			->get()
			->fetchAll(PDO::FETCH_ASSOC);

		$row['media_id'] = array_column($mediaRows, 'media_id');

		return $row;
	}

	/**
	 * Does this anime already exist in the collection?
	 *
	 * @param int|string $kitsuId
	 * @return bool
	 */
	public function has($kitsuId): bool
	{
		if ($this->validDatabase === FALSE)
		{
			return FALSE;
		}

		$row = $this->db->select('hummingbird_id')
			->from('anime_set')
			->where('hummingbird_id', $kitsuId)
			->get()
			->fetch(PDO::FETCH_ASSOC);

		return ! empty($row);
	}

	/**
	 * Get genres for anime collection items
	 *
	 * @param array $filter
	 * @return array
	 */
	public function getGenreList(array $filter = []): array
	{
		if ($this->validDatabase === FALSE)
		{
			return [];
		}

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

		$this->db->resetQuery();

		return $output;
	}

	private function updateMediaLink(string $animeId, array $media): void
	{
		// Delete the old entries
		$this->db->where('hummingbird_id', $animeId)
			->delete('anime_set_media_link');

		// Add the new entries
		$entries = [];
		foreach ($media as $id)
		{
			$entries[] = [
				'hummingbird_id' => $animeId,
				'media_id' => $id,
			];
		}

		$this->db->insertBatch('anime_set_media_link', $entries);
	}

	/**
	 * Update genre information for selected anime
	 *
	 * @param string $animeId The current anime
	 * @return void
	 */
	private function updateGenres($animeId): void
	{
		if ($this->validDatabase === FALSE)
		{
			return;
		}

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

			if ( ! in_array($flippedGenres[$animeGenre], $animeLinks, TRUE))
			{
				$linksToInsert[] = [
					'hummingbird_id' => $animeId,
					'genre_id' => $genreId,
				];
			}
		}

		if ( ! empty($linksToInsert))
		{
			try
			{
				$this->db->insertBatch('genre_anime_set_link', $linksToInsert);
			}
			catch (PDOException $e)
			{
				// This often results in a unique constraint violation
				// So swallow this for now
				// @TODO Fix properly
			}

		}
	}

	/**
	 * Add genres to the database
	 *
	 * @param array $genres
	 */
	private function addNewGenres(array $genres): void
	{
		if ($this->validDatabase === FALSE)
		{
			return;
		}

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
			$this->db->insertBatch('genres', $insert);
		}
		catch (PDOException $e)
		{
			// dump($e);
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
		if ($this->validDatabase === FALSE)
		{
			return [];
		}

		$genres = [];

		// Get existing genres
		$query = $this->db->select('id, genre')
			->from('genres')
			->get();

		foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $genre)
		{
			$genres[$genre['id']] = $genre['genre'];
		}

		$this->db->resetQuery();

		return $genres;
	}

	private function getExistingGenreLinkEntries(): array
	{
		if ($this->validDatabase === FALSE)
		{
			return [];
		}

		$links = [];

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

		$this->db->resetQuery();

		return $links;
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

		$query = $this->db->select('a.hummingbird_id, slug, title, alternate_title, show_type,
			 age_rating, episode_count, episode_length, cover_image, notes, media.type as media')
			->from('anime_set a')
			->join('anime_set_media_link ml', 'ml.hummingbird_id=a.hummingbird_id', 'inner')
			->join('media', 'media.id=ml.media_id', 'inner')
			->orderBy('media')
			->orderBy('title')
			->groupBy('a.hummingbird_id, media.type')
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
}
// End of AnimeCollectionModel.php