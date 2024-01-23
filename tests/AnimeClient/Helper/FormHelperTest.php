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

use Aviat\AnimeClient\Helper\Form as FormHelper;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

/**
 * @internal
 */
final class FormHelperTest extends AnimeClientTestCase
{
	public function testFormHelper(): void
	{
		$helper = new FormHelper();
		$helper->setContainer($this->container);

		$actual = $helper('input', [
			'type' => 'text',
			'value' => 'foo',
			'placeholder' => 'field',
			'name' => 'test',
		]);

		$this->assertMatchesSnapshot($actual);
	}
}
