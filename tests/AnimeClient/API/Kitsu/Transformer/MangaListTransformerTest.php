<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\MangaListTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\{
	FormItem,
	FormItemData
};
use Aviat\Ion\Json;

/**
 * @internal
 */
final class MangaListTransformerTest extends AnimeClientTestCase
{
	protected $dir;
	protected $rawBefore;
	protected $beforeTransform;
	protected $transformer;

	protected function setUp(): void
	{
		parent::setUp();

		$this->dir = AnimeClientTestCase::TEST_DATA_DIR . '/Kitsu';

		// Prep for transform
		$raw = Json::decodeFile("{$this->dir}/mangaListBeforeTransform.json");
		$this->beforeTransform = $raw['data']['findProfileBySlug']['library']['all']['nodes'];
		$this->transformer = new MangaListTransformer();
	}

	public function testTransform()
	{
		$actual = $this->transformer->transformCollection($this->beforeTransform);
		$this->assertMatchesSnapshot($actual);
	}

	public function testUntransform()
	{
		$input = [
			'id' => '15084773',
			'mal_id' => '26769',
			'chapters_read' => 67,
			'manga' => [
				'id' => '12345',
				'titles' => ['Bokura wa Minna Kawaisou'],
				'alternate_title' => NULL,
				'slug' => 'bokura-wa-minna-kawaisou',
				'url' => 'https://kitsu.io/manga/bokura-wa-minna-kawaisou',
				'type' => 'manga',
				'image' => 'https://media.kitsu.io/manga/poster_images/20286/small.jpg?1434293999',
				'genres' => [],
			],
			'status' => 'current',
			'notes' => '',
			'rereading' => FALSE,
			'reread_count' => 0,
			'new_rating' => 9,
		];

		$actual = $this->transformer->untransform($input);
		$expected = FormItem::from([
			'id' => '15084773',
			'mal_id' => '26769',
			'data' => FormItemData::from([
				'status' => 'current',
				'progress' => 67,
				'reconsuming' => FALSE,
				'reconsumeCount' => 0,
				'notes' => '',
				'ratingTwenty' => 18,
			]),
		]);

		$this->assertEquals($expected, $actual);
	}
}
