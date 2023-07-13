<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\UserTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Json;

/**
 * @internal
 */
final class UserTransformerTest extends AnimeClientTestCase
{
	protected array $beforeTransform;
	protected string $dir;

	protected function setUp(): void
	{
		parent::setUp();
		$this->dir = AnimeClientTestCase::TEST_DATA_DIR . '/Kitsu';

		$raw = Json::decodeFile("{$this->dir}/userBeforeTransform.json");
		$this->beforeTransform = $raw;
	}

	public function testTransform(): void
	{
		$actual = (new UserTransformer())->transform($this->beforeTransform);

		// Unset the time value that will change every day, so the test is consistent
		$actual->joinDate = '';

		$this->assertMatchesSnapshot($actual);
	}
}
