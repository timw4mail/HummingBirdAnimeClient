<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\Helper;

use Aviat\AnimeClient\Helper\Menu as MenuHelper;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

/**
 * @internal
 */
final class MenuHelperTest extends AnimeClientTestCase
{
	protected $helper;
	protected $urlGenerator;

	protected function setUp(): void
	{
		parent::setUp();
		$this->helper = $this->container->get('html-helper');
		$this->urlGenerator = $this->container->get('url-generator');
	}

	public function testInvoke()
	{
		$menus = [
			'no selection' => [
				'route_prefix' => '/foo',
				'items' => [
					'bar' => '/bar',
				],
			],
			'selected' => [
				'route_prefix' => '',
				'items' => [
					'index' => '/foobar',
				],
			],
		];

		$expected = [];

		// No selection
		$link = $this->helper->a($this->urlGenerator->url('/foo/bar'), 'Bar');
		$this->helper->ul()->rawItem($link);
		$expected['no selection'] = $this->helper->ul()->__toString();

		// selected
		$link = $this->helper->a($this->urlGenerator->url('/foobar'), 'Index', ['aria-current' => 'location']);
		$this->helper->ul()->rawItem($link, ['class' => 'selected']);
		$expected['selected'] = $this->helper->ul()->__toString();

		// Set config for tests
		$config = $this->container->get('config');
		$config->set('menus', $menus);

		$this->container->setInstance('config', $config);

		foreach ($menus as $case => $config)
		{
			if ($case === 'selected')
			{
				$this->setSuperGlobals([
					'_SERVER' => [
						'HTTP_HOST' => 'localhost',
						'REQUEST_URI' => '/foobar',
					],
				]);
			}
			else
			{
				$this->setSuperGlobals([
					'_SERVER' => [
						'HTTP_HOST' => 'localhost',
						'REQUEST_URI' => '/applesauceisgreat',
					],
				]);
			}

			$helper = new MenuHelper();
			$helper->setContainer($this->container);
			$this->assertSame($expected[$case], (string) $helper($case));
		}
	}
}
