<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeHistoryTransformer;
use Aviat\AnimeClient\API\Kitsu\Transformer\MangaHistoryTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Json;

class HistoryTransformerTest extends AnimeClientTestCase {
	protected array $beforeTransform;
	protected string $dir;

	public function setUp(): void	{
		parent::setUp();
		$this->dir = AnimeClientTestCase::TEST_DATA_DIR . '/Kitsu';

		$raw = Json::decodeFile("{$this->dir}/historyBeforeTransform.json");
		$this->beforeTransform = $raw;
	}

	public function testAnimeTransform(): void
	{
		$this->markTestSkipped("Old test data");

		$actual = (new AnimeHistoryTransformer())->transform($this->beforeTransform);
		$this->assertMatchesSnapshot($actual);
	}

	public function testMangaTransform(): void
	{
		$actual = (new MangaHistoryTransformer())->transform($this->beforeTransform);
		$this->assertMatchesSnapshot($actual);
	}
}