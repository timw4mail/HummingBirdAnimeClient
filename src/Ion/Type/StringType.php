<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Type;

use InvalidArgumentException;

/**
 * Slightly extended Stringy library
 */
final class StringType extends Stringy
{
	/**
	 * Alias for `create` static constructor
	 */
	public static function from(string $str = '', ?string $encoding = NULL): self
	{
		return self::create($str, $encoding);
	}

	/**
	 * See if two strings match, despite being delimited differently,
	 * such as camelCase, PascalCase, kebab-case, or snake_case.
	 *
	 * @throws InvalidArgumentException
	 */
	public function fuzzyCaseMatch(string $strToMatch): bool
	{
		$firstStr = (string) self::create($this->str)->dasherize();
		$secondStr = (string) self::create($strToMatch)->dasherize();

		return $firstStr === $secondStr;
	}
}

// End of StringType.php
