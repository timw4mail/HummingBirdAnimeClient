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

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\ParallelAPIRequest;
use Aviat\Ion\Friend;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ParallelAPIRequestTest extends TestCase
{
	public function testAddStringUrlRequest()
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest('https://httpbin.org');

		$friend = new Friend($requester);

		$this->assertSame($friend->requests, ['https://httpbin.org']);
	}

	public function testAddStringUrlRequests()
	{
		$requests = [
			'foo' => 'http://example.com',
			'bar' => 'https://example.com',
		];

		$requester = new ParallelAPIRequest();
		$requester->addRequests($requests);

		$friend = new Friend($requester);

		$this->assertSame($friend->requests, $requests);
	}
}
