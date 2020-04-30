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

namespace Aviat\AnimeClient\Types;

/**
 * Type representing an Anime object for display
 */
class FormItem extends AbstractType {
	/**
	 * @var string|int
	 */
	public $id;

	/**
	 * @var string
	 */
	public ?string $anilist_item_id;

	/**
	 * @var string|int
	 */
	public  $mal_id;

	/**
	 * @var FormItemData
	 */
	public ?FormItemData $data;

	public function setData($value): void
	{
		$this->data = FormItemData::from($value);
	}
}

