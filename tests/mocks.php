<?php
/**
 * All the mock classes that extend the classes they are used to test
 */

use Aviat\Ion\Enum;
use Aviat\Ion\Friend;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Transformer\AbstractTransformer;
use Aviat\Ion\View;
use Aviat\Ion\View\HtmlView;
use Aviat\Ion\View\HttpView;
use Aviat\Ion\View\JsonView;

use Aviat\AnimeClient\Model\Anime as AnimeModel;
use Aviat\AnimeClient\Model\API as BaseApiModel;


// -----------------------------------------------------------------------------
// Mock the default error handler
// -----------------------------------------------------------------------------

class MockErrorHandler {
	public function addDataTable($name, array $values=[]) {}
}

// -----------------------------------------------------------------------------
// Ion Mocks
// -----------------------------------------------------------------------------

class TestEnum extends Enum {
	const FOO = 'bar';
	const BAR = 'foo';
	const FOOBAR = 'baz';
}

class FriendGrandParentTestClass {
	protected $grandParentProtected = 84;
}

class FriendParentTestClass extends FriendGrandParentTestClass {
	protected $parentProtected = 47;
	private $parentPrivate = 654;
}

class FriendTestClass extends FriendParentTestClass {
	protected $protected = 356;
	private $private = 486;

	protected function getProtected()
	{
		return 4;
	}

	private function getPrivate()
	{
		return 23;
	}
}

class TestTransformer extends AbstractTransformer {
	
	public function transform($item)
	{
		$out = [];
		$genre_list = (array) $item;
		
		foreach($genre_list as $genre)
		{
			$out[] = $genre['name'];
		}
		
		return $out;
	}
}

class TestView extends View {}

trait MockViewOutputTrait {
	protected function output() {
		$reflect = new ReflectionClass($this);
		$properties = $reflect->getProperties();
		$props = [];

		foreach($properties as $reflectProp)
		{
			$reflectProp->setAccessible(TRUE);
			$props[$reflectProp->getName()] = $reflectProp->getValue($this);
		}

		$view = new TestView($this->container);
		$friend = new Friend($view);
		foreach($props as $name => $val)
		{
			$friend->__set($name, $val);
		}

		$friend->output();
	}
}

class TestHtmlView extends HtmlView {
	use MockViewOutputTrait;
}

class TestHttpView extends HttpView {
	use MockViewOutputTrait;
}

class TestJsonView extends JsonView {
	public function __destruct() {}
}

// -----------------------------------------------------------------------------
// AnimeClient Mocks
// -----------------------------------------------------------------------------
 
class MockBaseApiModel extends BaseApiModel {

	protected $base_url = 'https://httpbin.org/';

	public function __get($key)
	{
		return $this->$key;
	}
	
	public function __set($key, $value)
	{
		$this->$key = $value;
		return $this;
	}
}

class TestAnimeModel extends AnimeModel {

	protected $transformed_data_file;

	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->transformed_data_file = _dir(
			TEST_DATA_DIR, 'anime_list','anime-completed-transformed.json'
		);
	}

	protected function _get_list_from_api($status="all")
	{
		$data = json_decode(file_get_contents($this->transformed_data_file), TRUE);
		return $data;
	}
}
// End of mocks.php