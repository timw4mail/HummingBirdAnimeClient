<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use AnimeClient_TestCase;
use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeTransformer;
use Aviat\Ion\Friend;
use Aviat\Ion\Json;

class AnimeTransformerTest extends AnimeClient_TestCase {
	
	public function setUp()
	{
		parent::setUp();
		$this->dir = AnimeClient_TestCase::TEST_DATA_DIR . '/Kitsu';
		
		$this->beforeTransform = Json::decodeFile("{$this->dir}/animeBeforeTransform.json");
		$this->afterTransform = Json::decodeFile("{$this->dir}/animeAfterTransform.json");
		
		$this->transformer = new AnimeTransformer();
	}
	
	public function testTransform()
	{
		$expected = $this->afterTransform;
		$actual = $this->transformer->transform($this->beforeTransform);
		// Json::encodeFile("{$this->dir}/animeAfterTransform.json", $actual);
		
		$this->assertEquals($expected, $actual);
	}
}