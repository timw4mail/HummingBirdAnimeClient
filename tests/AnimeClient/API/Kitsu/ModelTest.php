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

namespace Aviat\AnimeClient\Tests\API\Kitsu;

use Aviat\AnimeClient\Tests\AnimeClientTestCase;

class ModelTest extends AnimeClientTestCase {

	protected $model;

	public function setUp(): void
	{
		parent::setup();
		$this->model = $this->container->get('kitsu-model');
	}

	public function testGetAnimeKitsuIdFromMALId(): void
	{
		$kitsuId = $this->model->getKitsuIdFromMALId("1", 'anime');
		self::assertEquals("1", $kitsuId);
	}

	public function testGetNullFromMALAnimeId(): void
	{
		$kitsuId = $this->model->getKitsuIdFromMALId("0", 'anime');
		self::assertNull($kitsuId);
	}
}