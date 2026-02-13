<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Component;

use Aviat\AnimeClient\Component\Tabs;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

final class TabsTest extends AnimeClientTestCase
{
	public function testInvokeSingleTab(): void
	{
		$component = new Tabs();
		$component->setContainer($this->container);

		$tabData = [
			'Label 1' => ['data' => 'content 1'],
		];
		$cb = fn ($data) => "<div>{$data['data']}</div>";

		$output = $component('test-tabs', $tabData, $cb);
		$this->assertStringContainsString('content 1', $output);
		$this->assertStringContainsString('single-tab', $output);
	}

	public function testInvokeMultipleTabs(): void
	{
		$component = new Tabs();
		$component->setContainer($this->container);

		$tabData = [
			'Label 1' => ['data' => 'content 1'],
			'Label 2' => ['data' => 'content 2'],
		];
		$cb = fn ($data) => "<div>{$data['data']}</div>";

		$output = $component('test-tabs', $tabData, $cb);
		$this->assertStringContainsString('content 1', $output);
		$this->assertStringContainsString('content 2', $output);
		$this->assertStringContainsString('Label 1', $output);
		$this->assertStringContainsString('Label 2', $output);
	}
}
