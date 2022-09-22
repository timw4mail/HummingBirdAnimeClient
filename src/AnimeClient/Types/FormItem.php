<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

/**
 * Type representing an Anime object for display
 */
class FormItem extends AbstractType
{
	public string|int $id;
	public string|int|NULL $mal_id;
	public string|int|NULL $anilist_id;
	public ?FormItemData $data;

	public function setData(mixed $value): void
	{
		$this->data = FormItemData::from($value);
	}
}
