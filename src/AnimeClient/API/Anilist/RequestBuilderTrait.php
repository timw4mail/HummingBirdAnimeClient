<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Anilist;

use Aviat\Ion\Di\ContainerAware;

trait RequestBuilderTrait {
	use ContainerAware;

	/**
	 * The request builder for the Anilist API
	 */
	protected RequestBuilder $requestBuilder;

	/**
	 * Set the request builder object
	 *
	 * @param RequestBuilder $requestBuilder
	 * @return self
	 */
	public function setRequestBuilder(RequestBuilder $requestBuilder): self
	{
		$this->requestBuilder = $requestBuilder;
		return $this;
	}
}