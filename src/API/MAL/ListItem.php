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

namespace Aviat\AnimeClient\API\MAL;

use Aviat\AnimeClient\API\AbstractListItem;
use Aviat\Ion\Di\ContainerAware;

/**
 * CRUD operations for MAL list items
 */
class ListItem extends AbstractListItem {
	use ContainerAware;
	use MALTrait;

	public function __construct()
	{
		$this->init();
	}

	public function create(array $data): bool
	{
		return FALSE;
	}

	public function delete(string $id): bool
	{
		return FALSE;
	}

	public function get(string $id): array
	{
		return [];
	}

	public function update(string $id, array $data): Response
	{

	}
}