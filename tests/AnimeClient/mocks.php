<?php declare(strict_types=1);
/**
 * All the mock classes that extend the classes they are used to test
 */

use Aviat\AnimeClient\Model\{
	API as BaseApiModel,
	Anime as AnimeModel,
	Manga as MangaModel
};
use Aviat\Ion\Transformer\AbstractTransformer;
use Aviat\Ion\View\{HtmlView, HttpView, JsonView};
use Aviat\Ion\{Enum, Friend, Json};

// -----------------------------------------------------------------------------
// Mock the default error handler
// -----------------------------------------------------------------------------

class MockErrorHandler
{
	public function addDataTable(string $name, array $values=[]): void
	{
	}
}

// -----------------------------------------------------------------------------
// Ion Mocks
// -----------------------------------------------------------------------------

class TestEnum extends Enum
{
	final public const FOO = 'bar';
	final public const BAR = 'foo';
	final public const FOOBAR = 'baz';
}

class FriendGrandParentTestClass
{
	protected int $grandParentProtected = 84;
}

class FriendParentTestClass extends FriendGrandParentTestClass
{
	protected int $parentProtected = 47;
	private int $parentPrivate = 654;
}

class FriendTestClass extends FriendParentTestClass
{
	protected int $protected = 356;
	private int $private = 486;

	protected function getProtected(): int
	{
		return 4;
	}

	private function getPrivate(): int
	{
		return 23;
	}
}

class TestTransformer extends AbstractTransformer
{
	public function transform($item): array
	{
		$out = [];
		$genre_list = (array) $item;

		foreach ($genre_list as $genre)
		{
			$out[] = $genre['name'];
		}

		return $out;
	}
}

trait MockViewOutputTrait
{
	protected function output(): void
	{
		$reflect = new ReflectionClass($this);
		$properties = $reflect->getProperties();
		$props = [];

		foreach ($properties as $reflectProp)
		{
			$reflectProp->setAccessible(TRUE);
			$props[$reflectProp->getName()] = $reflectProp->getValue($this);
		}

		$view = new TestView();
		$friend = new Friend($view);

		foreach ($props as $name => $val)
		{
			$friend->__set($name, $val);
		}

		$friend->output();
	}
}

class MockUtil
{
	public function get_cached_image($api_path, $series_slug, $type = 'anime'): string
	{
		return "/public/images/{$type}/{$series_slug}.jpg";
	}
}

class TestView extends HttpView
{
	public function send(): void
	{
	}

	protected function output(): void
	{
		/*$content =& $this->response->content;
		$content->set($this->output);
		$content->setType($this->contentType);
		$content->setCharset('utf-8');*/
	}
}

class TestHtmlView extends HtmlView
{
	use MockViewOutputTrait;
}

class TestHttpView extends HttpView
{
	use MockViewOutputTrait;
}

class TestJsonView extends JsonView
{
	public function __destruct()
	{
	}
}

// -----------------------------------------------------------------------------
// AnimeClient Mocks
// -----------------------------------------------------------------------------

trait MockInjectionTrait
{
	public function __get(string $key): mixed
	{
		return $this->{$key};
	}

	public function __set(string $key, mixed $value)
	{
		$this->{$key} = $value;

		return $this;
	}
}

class MockBaseApiModel extends BaseApiModel
{
	use MockInjectionTrait;

	protected string $base_url = 'https://httpbin.org/';

	protected function _get_list_from_api(string $status): array
	{
		return [];
	}
}

class TestAnimeModel extends AnimeModel
{
	use MockInjectionTrait;
}

class TestMangaModel extends MangaModel
{
	use MockInjectionTrait;

	protected function _check_cache($response)
	{
		$file = __DIR__ . '/test_data/manga_list/manga-transformed.json';

		return Json::decodeFile($file);
	}
}
// End of mocks.php
