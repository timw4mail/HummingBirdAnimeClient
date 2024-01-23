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

namespace Aviat\Ion\Tests;

use PHPUnit\Framework\Attributes\{IgnoreClassForCodeCoverage, Test};
use PHPUnit\Framework\TestCase;

use function Aviat\Ion\_dir;
use const DIRECTORY_SEPARATOR;

/**
 * @internal
 */
#[IgnoreClassForCodeCoverage(\Aviat\Ion\ImageBuilder::class)]
#[IgnoreClassForCodeCoverage(\Aviat\Ion\Attribute\Controller::class)]
#[IgnoreClassForCodeCoverage(\Aviat\Ion\Attribute\DefaultController::class)]
#[IgnoreClassForCodeCoverage(\Aviat\Ion\Attribute\Route::class)]
final class functionsTest extends TestCase
{
	#[Test]
	public function dir(): void
	{
		$args = ['foo', 'bar', 'baz'];
		$expected = implode(DIRECTORY_SEPARATOR, $args);

		$this->assertSame(_dir(...$args), $expected);
	}
}
