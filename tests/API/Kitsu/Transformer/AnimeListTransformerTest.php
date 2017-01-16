<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use AnimeClient_TestCase;
use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeListTransformer;
use Aviat\Ion\Friend;
use Aviat\Ion\Json;

class AnimeListTransformerTest extends AnimeClient_TestCase {
	
	public function setUp()
	{
		parent::setUp();
		$this->dir = AnimeClient_TestCase::TEST_DATA_DIR . '/Kitsu';
		
		$this->beforeTransform = Json::decodeFile("{$this->dir}/animeListItemBeforeTransform.json");
		$this->afterTransform = Json::decodeFile("{$this->dir}/animeListItemAfterTransform.json");
		
		$this->transformer = new AnimeListTransformer();
	}
	
	public function testTransform()
	{
		$expected = $this->afterTransform;
		$actual = $this->transformer->transform($this->beforeTransform);
		
		// Json::encodeFile("{$this->dir}/animeListItemAfterTransform.json", $actual);
		
		$this->assertEquals($expected, $actual);
	}
	
	public function dataUntransform()
	{
		return [[
			'input' => [
				'id' => 14047981,
				'watching_status' => 'current',
				'user_rating' => 8,
				'episodes_watched' => 38,
				'rewatched' => 0,
				'notes' => 'Very formulaic.',
				'edit' => true
			],
			'expected' => [
				'id' => 14047981,
				'data' => [
					'status' => 'current',
					'rating' => 4,
					'reconsuming' => false,
					'reconsumeCount' => 0,
					'notes' => 'Very formulaic.',
					'progress' => 38,
					'private' => false
				]
			]
		], [
			'input' => [
				'id' => 14047981,
				'watching_status' => 'current',
				'user_rating' => 8,
				'episodes_watched' => 38,
				'rewatched' => 0,
				'notes' => 'Very formulaic.',
				'edit' => 'true',
				'private' => 'On',
				'rewatching' => 'On'
			],
			'expected' => [
				'id' => 14047981,
				'data' => [
					'status' => 'current',
					'rating' => 4,
					'reconsuming' => true,
					'reconsumeCount' => 0,
					'notes' => 'Very formulaic.',
					'progress' => 38,
					'private' => true,
				]
			]
		]];
	}
	
	/**
	 * @dataProvider dataUntransform
	 */
	public function testUntransform($input, $expected)
	{
		$actual = $this->transformer->untransform($input);
		$this->assertEquals($expected, $actual);
	}
}