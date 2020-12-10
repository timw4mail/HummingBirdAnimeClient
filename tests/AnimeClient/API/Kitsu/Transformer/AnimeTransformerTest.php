<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Json;

class AnimeTransformerTest extends AnimeClientTestCase {

	protected $dir;
	protected $beforeTransform;
	protected $transformer;

	public function setUp(): void	{
		parent::setUp();
		$this->dir = AnimeClientTestCase::TEST_DATA_DIR . '/Kitsu';

		$this->beforeTransform = Json::decodeFile("{$this->dir}/animeBeforeTransform.json");

		$this->transformer = new AnimeTransformer();
	}

	public function testTransform()
	{
		$actual = $this->transformer->transform($this->beforeTransform);
		$this->assertMatchesSnapshot($actual);
	}
}