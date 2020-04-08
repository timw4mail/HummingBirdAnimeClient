<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.3
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests;

use Aviat\Ion\StringWrapper;
use Aviat\Ion\Type\StringType;
use PHPUnit\Framework\TestCase;

class StringWrapperTest extends TestCase {

	protected $wrapper;

	public function setUp(): void	{
		$this->wrapper = new class {
			use StringWrapper;
		};
	}

	public function testString()
	{
		$str = $this->wrapper->string('foo');
		$this->assertInstanceOf(StringType::class, $str);
	}

}