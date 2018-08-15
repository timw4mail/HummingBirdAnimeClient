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
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Anilist;

use Amp\Artax\Request;
use Aviat\AnimeClient\API\Mapping\{AnimeWatchingStatus, MangaReadingStatus};
use Aviat\AnimeClient\Types\FormItem;

/**
 * Anilist API Model
 */
final class Model
{
	use AnilistTrait;
	/**
	 * @var ListItem
	 */
	private $listItem;

	/**
	 * Constructor
	 *
	 * @param ListItem $listItem
	 */
	public function __construct(ListItem $listItem)
	{
		$this->listItem = $listItem;
	}

	public function getAnimeList()
	{
		$graphQL = <<<GQL
{
	MediaListCollection(userId: 103470, type: ANIME) {
    lists {
      entries {
        id
        mediaId
        score
        progress
        status
        media {
          id
          idMal
          title {
            romaji
            english
            native
            userPreferred
          }
          type
          format
          status
          episodes
          season
          genres
          synonyms
          countryOfOrigin
          source
          trailer {
            id
          }
          coverImage {
            large
            medium
          }
          bannerImage
          tags {
            id
          }
          externalLinks {
            id
          }
          mediaListEntry {
            id
          }
        }
        user {
          id
        }
      }
    }
  } 
}
GQL;
	}

	public function getMangaList()
	{
		$graphQL = <<<GQL
{
	MediaListCollection(userId: 103470, type: MANGA) {
    lists {
      entries {
        id
        mediaId
        score
        progress
        progressVolumes
        repeat
        private
        notes
        status
        media {
          id
          idMal
          title {
            romaji
            english
            native
            userPreferred
          }
          type
          format
          status
          chapters
          volumes
          genres
          synonyms
          countryOfOrigin
          source
          trailer {
            id
          }
          coverImage {
            large
            medium
          }
          bannerImage
          tags {
            id
          }
          externalLinks {
            id
          }
          mediaListEntry {
            id
          }
        }
        user {
          id
        }
      }
    }
  } 
}
GQL;

	}

	// -------------------------------------------------------------------------
	// ! Generic API calls
	// -------------------------------------------------------------------------

	/**
	 * Create a list item
	 *
	 * @param array $data
	 * @param string $type
	 * @return Request
	 */
	public function createListItem(array $data, string $type = 'anime'): Request
	{
		$createData = [];

		if ($type === 'anime') {
			$createData = [
				'id' => $data['id'],
				'data' => [
					'status' => AnimeWatchingStatus::KITSU_TO_ANILIST[$data['status']]
				]
			];
		} elseif ($type === 'manga') {
			$createData = [
				'id' => $data['id'],
				'data' => [
					'status' => MangaReadingStatus::KITSU_TO_ANILIST[$data['status']]
				]
			];
		}

		return $this->listItem->create($createData, $type);
	}

	/**
	 * Get the data for a specific list item, generally for editing
	 *
	 * @param string $listId - The unique identifier of that list item
	 * @return mixed
	 */
	public function getListItem(string $listId)
	{
		// @TODO: implement
	}

	/**
	 * Modify a list item
	 *
	 * @param FormItem $data
	 * @return Request
	 */
	public function updateListItem(FormItem $data): Request
	{
		return $this->listItem->update($data['id'], $data['data']);
	}

	/**
	 * Remove a list item
	 *
	 * @param string $id - The id of the list item to remove
	 * @return Request
	 */
	public function deleteListItem(string $id): Request
	{
		return $this->listItem->delete($id);
	}
}