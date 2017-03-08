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

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\JsonAPI;
use Aviat\AnimeClient\API\Kitsu\Transformer\MangaTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Json;

class MangaTransformerTest extends AnimeClientTestCase {
	
	protected $dir;
	protected $beforeTransform;
	protected $afterTransform;
	protected $transformer;

	public function setUp() 
	{
		parent::setUp();
		$this->dir = AnimeClientTestCase::TEST_DATA_DIR . '/Kitsu';
		
		$data = Json::decodeFile("{$this->dir}/mangaBeforeTransform.json");
		$baseData = $data['data'][0]['attributes'];
		$baseData['included'] = $data['included'];
		$this->beforeTransform = $baseData;
		$this->afterTransform = Json::decodeFile("{$this->dir}/mangaAfterTransform.json");
		
		$this->transformer = new MangaTransformer();
	}
	
	public function testTransform()
	{
		$actual = $this->transformer->transform($this->beforeTransform);
		$expected = $this->afterTransform;
		//Json::encodeFile("{$this->dir}/mangaAfterTransform.json", $actual);
		
		$this->assertEquals($expected, $actual);
	}
}