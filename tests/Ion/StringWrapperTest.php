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