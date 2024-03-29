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

namespace Aviat\AnimeClient\Tests;

use PDO;

/**
 * @internal
 */
final class RequirementsTest extends AnimeClientTestCase
{
	public function testPHPVersion(): void
	{
		$this->assertTrue(PHP_VERSION_ID >= 8);
	}

	public function testHasPDO(): void
	{
		$this->assertTrue(class_exists('PDO'));
	}

	public function testHasPDOSqlite(): void
	{
		$drivers = PDO::getAvailableDrivers();
		$this->assertTrue(in_array('sqlite', $drivers, TRUE));
	}
}
