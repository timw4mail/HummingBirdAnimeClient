<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Type;

use Stringy\Stringy;

/**
 * Wrapper around Stringy
 */
class StringType extends Stringy {

	/**
	 * Alias for `create` static constructor
	 *
	 * @param string $str
	 * @return self
	 */
	public static function from(string $str): self
	{
		return self::create($str);
	}

	/**
	 * See if two strings match, despite being delimited differently,
	 * such as camelCase, PascalCase, kebab-case, or snake_case.
	 *
	 * @param string $strToMatch
	 * @throws \InvalidArgumentException
	 * @return boolean
	 */
	public function fuzzyCaseMatch(string $strToMatch): bool
	{
		$firstStr = (string)self::create($this->str)->dasherize();
		$secondStr = (string)self::create($strToMatch)->dasherize();

		return $firstStr === $secondStr;
	}
}
// End of StringType.php