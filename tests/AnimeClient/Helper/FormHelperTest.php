<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
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
