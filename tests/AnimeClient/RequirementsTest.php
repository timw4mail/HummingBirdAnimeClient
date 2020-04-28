<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use PDO;

class RequirementsTest extends AnimeClientTestCase {

	public function testPHPVersion(): void
	{
		$this->assertTrue(version_compare(PHP_VERSION, "5.4", "ge"));
	}

	public function testHasPDO(): void
	{
		$this->assertTrue(class_exists('PDO'));
	}

	public function testHasPDOSqlite(): void
	{
		$drivers = PDO::getAvailableDrivers();
		$this->assertTrue(in_array('sqlite', $drivers));
	}
}