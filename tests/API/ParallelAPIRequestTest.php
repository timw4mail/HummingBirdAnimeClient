<?php

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\{APIRequestBuilder, ParallelAPIRequest};
use Aviat\Ion\Friend;
use PHPUnit\Framework\TestCase;

class ParallelAPIRequestsTest extends TestCase {
	
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