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

namespace Aviat\Ion\Tests\Exception;

use Aviat\Ion\Exception\DoubleRenderException;
use Aviat\Ion\Tests\IonTestCase;

class DoubleRenderExceptionTest extends IonTestCase {

	public function testDefaultMessage()
	{
		$this->expectException(DoubleRenderException::class);
		$this->expectExceptionMessage('A view can only be rendered once, because headers can only be sent once.');

		throw new DoubleRenderException();
	}
}