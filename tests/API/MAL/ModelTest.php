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

namespace Aviat\AnimeClient\Tests\API\MAL;

use Aviat\AnimeClient\Tests\AnimeClientTestCase;

class ModelTest extends AnimeClientTestCase {

	protected $model;

	public function setUp()
	{
		parent::setUp();
		$this->model = $this->container->get('mal-model');
	}

	public function testGetListItem()
	{
		$this->assertEquals([], $this->model->getListItem('foo'));
	}
}