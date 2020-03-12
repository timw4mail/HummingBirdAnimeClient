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

namespace Aviat\Ion\Tests\Model;

use Aviat\Ion\Model\DB as BaseDBModel;
use Aviat\Ion\Tests\Ion_TestCase;

class BaseDBModelTest extends Ion_TestCase {

	public function testBaseDBModelSanity()
	{
		$baseDBModel = new BaseDBModel($this->container->get('config'));
		$this->assertTrue(is_object($baseDBModel));
	}
}