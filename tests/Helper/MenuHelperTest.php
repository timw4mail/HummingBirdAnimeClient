<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\Helper\Menu as MenuHelper;

class MenuHelperTest extends AnimeClient_TestCase {

	public function setUp()
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
					'bar' => '/bar'
				]
			],
			'selected' => [
				'route_prefix' => '',
				'items' => [
					'index' => '/foobar'
				]
			]
		];

		$expected = [];

		// No selection
		$link = $this->helper->a($this->urlGenerator->url('/foo/bar'), 'Bar');
		$this->helper->ul()->rawItem($link);
		$expected['no selection'] = $this->helper->ul()->__toString();

		// selected
		$link = $this->helper->a($this->urlGenerator->url('/foobar'), 'Index');
		$this->helper->ul()->rawItem($link, ['class' => 'selected']);
		$expected['selected'] = $this->helper->ul()->__toString();

		// Set config for tests
		$config = $this->container->get('config');
		$config->set('menus', $menus);
		$this->container->setInstance('config', $config);

		foreach($menus as $case => $config)
		{
			if ($case === 'selected')
			{
				$this->setSuperGlobals([
					'_SERVER' => [
						'HTTP_HOST' => 'localhost',
						'REQUEST_URI' => '/foobar'
					]
				]);
			}
			else
			{
				$this->setSuperGlobals([
					'_SERVER' => [
						'HTTP_HOST' => 'localhost',
						'REQUEST_URI' => '/applesauceisgreat'
					]
				]);
			}

			$helper = new MenuHelper();
			$helper->setContainer($this->container);
			$this->assertEquals($expected[$case], (string)$helper($case));
		}
	}
}