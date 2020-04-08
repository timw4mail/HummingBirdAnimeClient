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

namespace Aviat\Ion\Tests\Model;

use Aviat\Ion\Model\DB as BaseDBModel;
use Aviat\Ion\Tests\IonTestCase;

class BaseDBModelTest extends IonTestCase {

	public function testBaseDBModelSanity()
	{
		$baseDBModel = new BaseDBModel($this->container->get('config'));
		$this->assertTrue(is_object($baseDBModel));
	}
}