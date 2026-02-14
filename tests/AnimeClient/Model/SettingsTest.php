<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Model;

use Aviat\AnimeClient\Model\Settings;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

final class SettingsTest extends AnimeClientTestCase
{
	protected Settings $model;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();
		$this->model = $this->container->get('settings-model');
	}

	public function testGetSettings(): void
	{
		$settings = $this->model->getSettings();
		$this->assertIsArray($settings);
	}

	public function testGetSettingsForm(): void
	{
		$form = $this->model->getSettingsForm();
		$this->assertIsArray($form);
	}

	public function testSaveSettingsFile(): void
	{
		$root = self::TEST_DATA_DIR;
		$this->container->get('config')->set('root', $root);
		$settingsPath = $root . '/app/config';
		if (! is_dir($settingsPath))
		{
			mkdir($settingsPath, 0777, true);
		}

		$this->assertTrue($this->model->saveSettingsFile([
			'config' => [
				'whose_list' => 'Test User',
				'root' => $root,
			],
		]));
		$this->assertFileExists($settingsPath . '/admin-override.toml');

		unlink($settingsPath . '/admin-override.toml');
		rmdir($settingsPath);
		rmdir($root . '/app');
	}

	public function testSaveSettingsFileError(): void
	{
		$this->container->get('config')->set('root', '/non/existent/path');
		$this->assertFalse($this->model->saveSettingsFile(['config' => ['foo' => 'bar']]));
	}
}
