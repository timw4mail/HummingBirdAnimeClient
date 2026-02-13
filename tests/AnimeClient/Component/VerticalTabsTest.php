<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Component;

use Aviat\AnimeClient\Component\VerticalTabs;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

final class VerticalTabsTest extends AnimeClientTestCase
{
	public function testInvoke(): void
	{
		$component = new VerticalTabs();
		$component->setContainer($this->container);

		$tabData = [
			'Label 1' => ['data' => 'content 1'],
			'Label 2' => ['data' => 'content 2'],
		];
		$cb = fn ($data) => "<div>{$data['data']}</div>";

		$output = $component('test-vtabs', $tabData, $cb);
		$this->assertStringContainsString('content 1', $output);
		$this->assertStringContainsString('content 2', $output);
		$this->assertStringContainsString('Label 1', $output);
		$this->assertStringContainsString('Label 2', $output);
	}
}
