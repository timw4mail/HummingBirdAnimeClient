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

namespace Aviat\AnimeClient\API\Kitsu;

trait RequestBuilderTrait
{
	/**
	 * The request builder for the Kitsu API
	 */
	protected RequestBuilder $requestBuilder;

	/**
	 * Set the request builder object
	 *
	 * @return ListItem|Model|RequestBuilderTrait
	 */
	public function setRequestBuilder(RequestBuilder $requestBuilder): self
	{
		$this->requestBuilder = $requestBuilder;

		return $this;
	}
}
