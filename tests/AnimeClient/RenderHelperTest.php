<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\RenderHelper;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class RenderHelperTest extends AnimeClientTestCase
{
	protected RenderHelper $helper;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();
		$this->container
			->get('session')
			->getSegment(\Aviat\AnimeClient\SESSION_SEGMENT)
			->clear();
		$this->helper = new RenderHelper($this->container);
	}

	public function testIsAuthenticated(): void
	{
		$this->assertFalse($this->helper->isAuthenticated());
	}

	public function testAssetUrl(): void
	{
		$url = $this->helper->assetUrl('images', 'test.png');
		$this->assertEquals('https://localhost/assets/images/test.png', $url);
	}

	public function testUrlFromRoute(): void
	{
		$url = $this->helper->urlFromRoute('login');
		$this->assertEquals('/login', $url);
	}

	public function testDefaultUrl(): void
	{
		$this->container->get('config')->set('default_list', 'anime');
		$this->container->get('config')->set('default_anime_list_path', 'watching');
		$url = $this->helper->defaultUrl('anime');
		$this->assertEquals('https://localhost/anime/watching', $url);
	}

	public function testLastSegment(): void
	{
		$this->setSuperGlobals(['_SERVER' => ['REQUEST_URI' => '/anime/details/foo']]);
		$this->helper = $this->container->get('render-helper');
		$this->assertEquals('foo', $this->helper->lastSegment());
	}

	public function testUrlFromPath(): void
	{
		$url = $this->helper->urlFromPath('/foo');
		$this->assertEquals('https://localhost/foo', $url);
	}

	public function testIsViewPage(): void
	{
		$this->setSuperGlobals(['_SERVER' => ['REQUEST_URI' => '/anime/watching']]);
		$this->helper = $this->container->get('render-helper');
		$this->assertTrue($this->helper->isViewPage());

		$this->setSuperGlobals(['_SERVER' => ['REQUEST_URI' => '/anime/add']]);
		$this->helper = $this->container->get('render-helper');
		$this->assertFalse($this->helper->isViewPage());
	}
}
