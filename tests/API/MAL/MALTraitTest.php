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

use Aviat\AnimeClient\API\MAL\MALRequestBuilder;
use Aviat\AnimeClient\API\MAL\MALTrait;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Di\ContainerAware;

class MALTraitTest extends AnimeClientTestCase {

	protected $obj;

	public function setUp()
	{
		parent::setUp();
		$this->obj = new class {
			use ContainerAware;
			use MALTrait;
		};
		$this->obj->setContainer($this->container);
		$this->obj->setRequestBuilder(new MALRequestBuilder());
	}

	public function testSetupRequest()
	{
		$request = $this->obj->setUpRequest('GET', 'foo', [
			'query' => [
				'foo' => 'bar'
			],
			'body' => ''
		]);
		$this->assertInstanceOf(\Amp\Artax\Request::class, $request);
		$this->assertEquals($request->getUri(), 'https://myanimelist.net/api/foo?foo=bar');
	}
}