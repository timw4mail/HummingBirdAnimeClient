<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\RenderHelper;

final class RenderHelperTest extends AnimeClientTestCase
{
	protected RenderHelper $helper;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();
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

	public function testIsViewPage(): void
	{
		$this->setSuperGlobals(['_SERVER' => ['REQUEST_URI' => '/anime/watching']]);
		$this->helper = new RenderHelper($this->container);
		$this->assertTrue($this->helper->isViewPage());

		$this->setSuperGlobals(['_SERVER' => ['REQUEST_URI' => '/anime/add']]);
		$this->helper = new RenderHelper($this->container);
		$this->assertFalse($this->helper->isViewPage());
	}
}
