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

namespace Aviat\Ion\Tests\Exception;

use Aviat\Ion\Exception\DoubleRenderException;
use Aviat\Ion\Tests\IonTestCase;

/**
 * @internal
 */
final class DoubleRenderExceptionTest extends IonTestCase
{
	public function testDefaultMessage(): never
	{
		$this->expectException(DoubleRenderException::class);
		$this->expectExceptionMessage('A view can only be rendered once, because headers can only be sent once.');

		throw new DoubleRenderException();
	}
}
