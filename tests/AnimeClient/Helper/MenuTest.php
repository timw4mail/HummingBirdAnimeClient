<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Helper;

use Aviat\AnimeClient\Helper\Menu;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

/**
 * @internal
 */
final class MenuTest extends AnimeClientTestCase
{
	public function testMenu(): void
	{
		$menu = new Menu();
		$menu->setContainer($this->container);

		$this->container->get('config')->set('menus', [
			'test' => [
				'route_prefix' => '',
				'items' => [
					'foo' => 'bar',
				],
			],
		]);

		$html = $menu('test');
		$this->assertStringContainsString('Foo', $html);
		$this->assertStringContainsString('/bar', $html);
	}
}
