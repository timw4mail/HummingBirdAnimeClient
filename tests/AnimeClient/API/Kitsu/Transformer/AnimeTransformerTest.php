<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Json;

/**
 * @internal
 */
final class AnimeTransformerTest extends AnimeClientTestCase
{
	protected $dir;
	protected $beforeTransform;
	protected $transformer;

	protected function setUp(): void
	{
		parent::setUp();
		$this->dir = AnimeClientTestCase::TEST_DATA_DIR . '/Kitsu';

		$this->beforeTransform = Json::decodeFile("{$this->dir}/animeBeforeTransform.json");

		$this->transformer = new AnimeTransformer();
	}

	public function testTransform(): never
	{
		$this->markTestSkipped('May fail on CI');
		$actual = $this->transformer->transform($this->beforeTransform);
		$this->assertMatchesSnapshot($actual);
	}
}
