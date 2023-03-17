<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\View;

use Aviat\Ion\{HttpViewInterface, Json};

/**
 * View class to serialize Json
 */
class JsonView extends HttpView
{
	/**
	 * Response mime type
	 */
	protected string $contentType = 'application/json';

	/**
	 * Set the output string
	 */
	public function setOutput(mixed $string): HttpViewInterface
	{
		if ( ! is_string($string))
		{
			$string = Json::encode($string);
		}

		return parent::setOutput($string);
	}
}

// End of JsonView.php
