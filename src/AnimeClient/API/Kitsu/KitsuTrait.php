<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

trait KitsuTrait {
	/**
	 * The request builder for the Kitsu GraphQL API
	 * @var KitsuRequestBuilder
	 */
	protected ?KitsuRequestBuilder $requestBuilder = null;

	/**
	 * The request builder for the Kitsu API
	 * @var KitsuJsonApiRequestBuilder
	 */
	protected KitsuJsonApiRequestBuilder $jsonApiRequestBuilder;

	/**
	 * Set the GraphQL request builder object
	 *
	 * @param KitsuRequestBuilder $requestBuilder
	 * @return $this
	 */
	public function setRequestBuilder(KitsuRequestBuilder $requestBuilder): self
	{
		$this->requestBuilder = $requestBuilder;
		return $this;
	}

	/**
	 * Set the request builder object
	 *
	 * @param KitsuJsonApiRequestBuilder $requestBuilder
	 * @return self
	 */
	public function setJsonApiRequestBuilder($requestBuilder): self
	{
		$this->jsonApiRequestBuilder = $requestBuilder;
		return $this;
	}
}