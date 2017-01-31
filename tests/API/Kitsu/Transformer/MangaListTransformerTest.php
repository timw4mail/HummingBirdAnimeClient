<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use AnimeClient_TestCase;
use Aviat\AnimeClient\API\JsonAPI;
use Aviat\AnimeClient\API\Kitsu\Transformer\MangaListTransformer;
use Aviat\Ion\Json;

class MangaListTransformerTest extends AnimeClient_TestCase {

	public function setUp() 
	{
		parent::setUp();
		$this->dir = AnimeClient_TestCase::TEST_DATA_DIR . '/Kitsu';
		
		$rawBefore = Json::decodeFile("{$this->dir}/mangaListBeforeTransform.json");
		$this->beforeTransform = JsonAPI::inlineRawIncludes($rawBefore, 'manga');
		$this->afterTransform = Json::decodeFile("{$this->dir}/mangaListAfterTransform.json");
		
		$this->transformer = new MangaListTransformer();
	}
	
	public function testTransform()
	{
		$expected = $this->afterTransform;
		$actual = $this->transformer->transformCollection($this->beforeTransform);
		
		// Json::encodeFile("{$this->dir}/mangaListAfterTransform.json", $actual);
		
		$this->assertEquals($expected, $actual);
	}
	
	public function testUntransform()
	{
		$input = [
			'id' => "15084773",
			'chapters_read' => 67,
			'manga' => [
				'titles' => ["Bokura wa Minna Kawaisou"],
				'alternate_title' => NULL,
				'slug' => "bokura-wa-minna-kawaisou",
				'url' => "https://kitsu.io/manga/bokura-wa-minna-kawaisou",
				'type' => 'manga',
				'image' => 'https://media.kitsu.io/manga/poster_images/20286/small.jpg?1434293999',
				'genres' => [],
			],
			'status' => 'current',
			'notes' => '',
			'rereading' => false,
			'reread_count' => 0,
			'new_rating' => 9,
		];
		
		$actual = $this->transformer->untransform($input);
		$expected = [
			'id' => '15084773',
			'data' => [
				'status' => 'current',
				'progress' => 67,
				'reconsuming' => false,
				'reconsumeCount' => 0,
				'notes' => '',
				'rating' => 4.5
			]
		];
		
		$this->assertEquals($expected, $actual);
	}

}