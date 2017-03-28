<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\ControllerTrait;

class ControllerTraitTest extends AnimeClientTestCase {

	public function setUp()
	{
		parent::setUp();

		$this->controller = new class {
			use ControllerTrait;
		};
	}

	public function testFormatTitle()
	{
		$this->assertEquals(
			$this->controller->formatTitle('foo', 'bar', 'baz'),
			'foo &middot; bar &middot; baz'
		);
	}
}