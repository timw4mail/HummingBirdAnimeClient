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

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\{APIRequestBuilder, ParallelAPIRequest};
use Aviat\Ion\Friend;
use PHPUnit\Framework\TestCase;

class ParallelAPIRequestTest extends TestCase {
	
	public function testAddStringUrlRequest()
	{
		$requester = new ParallelAPIRequest();
		$friend = new Friend($requester);
		$friend->addRequest('https://httpbin.org');
		
		$this->assertEquals($friend->requests, ['https://httpbin.org']);
	}
	
	public function testAddStringUrlRequests()
	{
		$requests = [
			'foo' => 'http://example.com',
			'bar' => 'https://example.com'
		];
		
		$requester = new ParallelAPIRequest();
		$friend = new Friend($requester);
		$friend->addRequests($requests);
		
		$this->assertEquals($friend->requests, $requests);
	}
}