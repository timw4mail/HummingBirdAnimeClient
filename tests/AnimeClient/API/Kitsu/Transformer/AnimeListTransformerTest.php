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

use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeListTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Json;

/**
 * @internal
 */
final class AnimeListTransformerTest extends AnimeClientTestCase
{
	protected string $dir;
	protected array $beforeTransform;
	protected AnimeListTransformer $transformer;

	protected function setUp(): void
	{
		parent::setUp();
		$this->dir = AnimeClientTestCase::TEST_DATA_DIR . '/Kitsu';

		$raw = Json::decodeFile("{$this->dir}/animeListItemBeforeTransform.json");
		$this->beforeTransform = $raw['data']['findLibraryEntryById'];

		$this->transformer = new AnimeListTransformer();
	}

	public function testTransform(): void
	{
		$this->markTestSkipped('Old test data');

		$actual = $this->transformer->transform($this->beforeTransform);
		$this->assertMatchesSnapshot($actual);
	}

	public function dataUntransform(): array
	{
		return [[
			'input' => [
				'id' => 14047981,
				'watching_status' => 'current',
				'user_rating' => 8,
				'episodes_watched' => 38,
				'rewatched' => 0,
				'notes' => 'Very formulaic.',
				'edit' => TRUE,
			],
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
				'rewatching' => 'On',
			],
		], [
			'input' => [
				'id' => 14047983,
				'mal_id' => '12347',
				'watching_status' => 'current',
				'user_rating' => 0,
				'episodes_watched' => 12,
				'rewatched' => 0,
				'notes' => '',
				'edit' => 'true',
				'private' => 'On',
				'rewatching' => 'On',
			],
		]];
	}

	/**
	 * @dataProvider dataUntransform
	 */
	public function testUntransform(array $input): void
	{
		$actual = $this->transformer->untransform($input);
		$this->assertMatchesSnapshot($actual);
	}
}
