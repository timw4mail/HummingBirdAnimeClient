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

namespace Aviat\AnimeClient\Tests\API\MAL\Transformer;

use Aviat\AnimeClient\API\MAL\Transformer\AnimeListTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Friend;
use Aviat\Ion\Json;

class AnimeListTransformerTest extends AnimeClientTestCase {

	protected $transformer;

	public function setUp()
	{
		parent::setUp();
		$this->transformer = new AnimeListTransformer();
	}

	public function testTransform()
	{
		$this->assertEquals([], $this->transformer->transform([]));
	}

}