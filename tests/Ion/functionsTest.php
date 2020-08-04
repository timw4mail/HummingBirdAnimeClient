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
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests;

use function Aviat\Ion\_dir;

use PHPUnit\Framework\TestCase;

class functionsTest extends TestCase {


	public function test_dir()
	{
		$args = ['foo', 'bar', 'baz'];
		$expected = implode(\DIRECTORY_SEPARATOR, $args);

		$this->assertEquals(_dir(...$args), $expected);
	}
}