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

use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeListTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Friend;
use Aviat\Ion\Json;

class AnimeListTransformerTest extends AnimeClientTestCase {
	
	protected $dir;
	protected $beforeTransform;
	protected $afterTransform;
	protected $transformer;
	
	public function setUp()
	{
		parent::setUp();
		$this->dir = AnimeClientTestCase::TEST_DATA_DIR . '/Kitsu';
		
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
				'mal_id' => null,
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
				'mal_id' => '12345',
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
				'mal_id' => '12345',
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