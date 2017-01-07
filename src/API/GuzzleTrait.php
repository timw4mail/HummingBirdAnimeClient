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
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

/**
 * Base trait for api interaction
 */
trait GuzzleTrait {
    /**
     * The Guzzle http client object
     * @var object
     */
    protected $client;

    /**
     * Cookie jar object for api requests
     * @var object
     */
    protected $cookieJar;

    /**
     * Set up the class properties
     *
     * @return void
     */
    abstract protected function init();
}