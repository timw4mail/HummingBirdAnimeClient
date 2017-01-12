<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use AnimeClient_TestCase;
use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeTransformer;
use Aviat\Ion\Friend;
use Aviat\Ion\Json;

class AnimeTransformerTest extends AnimeClient_TestCase {
	
	public function setUp()
	{
		parent::setUp();
		$dir = AnimeClient_TestCase::TEST_DATA_DIR . '/Kitsu';
		
		//$this->beforeTransform = Json::decodeFile("{$dir}/animeBeforeTransform.json");
		//$this->afterTransform = Json::decodeFile("{$dir}/animeAfterTransform.json");
		
		$this->transformer = new AnimeTransformer();
	}
	
	public function testTransform()
	{
		/*$expected = $this->afterTransform;
		$actual = $this->transformer->transform($this->beforeTransform);
		
		$this->assertEquals($expected, $actual);*/
	}
}