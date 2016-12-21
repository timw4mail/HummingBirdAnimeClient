<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package	 AnimeListClient
 * @author	  Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license	 http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version	 4.0
 * @link		https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Aviat\AnimeClient\API\AbstractListItem;

class KitsuAnimeListItem extends AbstractListItem {
	use KitsuTrait;

	public function create(array $data): bool
	{
		// TODO: Implement create() method.
	}

	public function delete(string $id): bool
	{
		// TODO: Implement delete() method.
	}

	public function get(string $id): array
	{
		// TODO: Implement get() method.
	}

	public function update(string $id, array $data): bool
	{
		// TODO: Implement update() method.
	}
}