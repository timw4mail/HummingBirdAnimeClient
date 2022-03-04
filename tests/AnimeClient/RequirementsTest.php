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

namespace Aviat\AnimeClient\Tests;

use PDO;

/**
 * @internal
 */
final class RequirementsTest extends AnimeClientTestCase
{
	public function testPHPVersion(): void
	{
		$this->assertTrue(version_compare(PHP_VERSION, '8', 'ge'));
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
