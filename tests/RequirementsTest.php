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
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use PDO;

class RequirementsTest extends AnimeClientTestCase {

	public function testPHPVersion()
	{
		$this->assertTrue(version_compare(PHP_VERSION, "5.4", "ge"));
	}

	public function testHasPDO()
	{
		$this->assertTrue(class_exists('PDO'));
	}

	public function testHasPDOSqlite()
	{
		$drivers = PDO::getAvailableDrivers();
		$this->assertTrue(in_array('sqlite', $drivers));
	}
}