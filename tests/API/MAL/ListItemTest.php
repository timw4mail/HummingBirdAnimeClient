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

namespace Aviat\AnimeClient\Tests\API\MAL;

use Aviat\AnimeClient\API\MAL\ListItem;
use Aviat\AnimeClient\API\MAL\MALRequestBuilder;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Di\ContainerAware;

class ListItemTest extends AnimeClientTestCase {

	protected $listItem;

	public function setUp()
	{
		parent::setUp();
		$this->listItem = new ListItem();
		$this->listItem->setContainer($this->container);
		$this->listItem->setRequestBuilder(new MALRequestBuilder());
	}

	public function testGet()
	{
		$this->assertEquals([], $this->listItem->get('foo'));
	}
}