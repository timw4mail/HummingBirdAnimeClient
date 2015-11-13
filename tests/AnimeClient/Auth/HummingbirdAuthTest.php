<?php
use Aura\Session\SessionFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

use Aviat\Ion\Friend;
use Aviat\AnimeClient\Auth\HummingbirdAuth;

class HummingbirdAuthTest extends AnimeClient_TestCase {

	static $session;
	static $sessionHandler;

	public static function setUpBeforeClass()
	{
		self::$session = (new SessionFactory)->newInstance([]);
	}

	public function setUp()
	{
		parent::setUp();
		$auth = new HummingbirdAuth($this->container);
		$friend = new Friend($auth);
		$this->auth = $friend;
		$this->container->set('session', self::$session);
	}

	public function dataAuthenticate()
	{
		$testToken = 'notReallyAValidTokenButThisIsATest';

		return [
			'successful auth call' => [
				'username' => 'timw4mailtest',
				'password' => 'password',
				'response_data' => [
					'code' => 201,
					'body' => json_encode($testToken)
				],
				'session_value' => $testToken,
				'expected' => TRUE,
			],
			'unsuccessful auth call' => [
				'username' => 'foo',
				'password' => 'foobarbaz',
				'response_data' => [
					'code' => 401,
					'body' => '{"error":"Invalid credentials"}',
				],
				'session_value' => FALSE,
				'expected' => FALSE,
			]
		];
	}

	/**
	 * @dataProvider dataAuthenticate
	 */
	public function testAuthenticate($username, $password, $response_data, $session_value, $expected)
	{
		$this->container->get('config')
			->set('hummingbird_username', $username);
		$model = new MockBaseApiModel($this->container);
		$mock = new MockHandler([
			new Response($response_data['code'], [], $response_data['body'])
		]);
		$handler = HandlerStack::create($mock);
		$client = new Client([
			'handler' => $handler,
			'http_errors' => FALSE // Don't throw an exception for 400/500 class status codes
		]);
		$model->__set('client', $client);
		$this->auth->__set('model', $model);

		$actual = $this->auth->authenticate($password);
		$this->assertEquals($expected, $actual);
	}

	public function testIsAuthenticated()
	{
		$data = $this->dataAuthenticate();
		call_user_func_array([$this, 'testAuthenticate'], $data['successful auth call']);
		$this->assertTrue($this->auth->is_authenticated());
		$this->auth->logout();
		$this->assertFalse($this->auth->is_authenticated());
	}
}