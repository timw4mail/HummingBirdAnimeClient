<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion;

/**
 * An Enum Trait to have the same functionality as \Aviat\Ion\ConstList
 */
trait EnumTrait {
	public function __invoke(): int|string
	{
		return $this->value;
	}

	/**
	 * @return array<string>
	 */
	public static function getConstList(): array
	{
		return array_column(self::cases(), 'name');
	}

	public static function isValid(mixed $key): bool
	{
		return in_array($key, self::getConstList());
	}
}