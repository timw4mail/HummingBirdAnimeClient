<?php declare(strict_types=1);
/**
 * Ion
 *
 * Building blocks for web development
 *
 * PHP version 7.2
 *
 * @package     Ion
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.0.0
 * @link        https://git.timshomepage.net/aviat/ion
 */

namespace Aviat\Ion\Type;

use Stringy\Stringy;

/**
 * Wrapper around Stringy
 */
class StringType extends Stringy {

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