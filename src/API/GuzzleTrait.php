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

use Psr\Http\Message\ResponseInterface;

/**
 * Base trait for api interaction
 *
 * @method ResponseInterface get(string $uri, array $options);
 * @method ResponseInterface delete(string $uri, array $options);
 * @method ResponseInterface head(string $uri, array $options);
 * @method ResponseInterface options(string $uri, array $options);
 * @method ResponseInterface patch(string $uri, array $options);
 * @method ResponseInterface post(string $uri, array $options);
 * @method ResponseInterface put(string $uri, array $options);
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

    /**
     * Magic methods to call guzzle api client
     *
     * @param  string $method
     * @param  array $args
     * @return ResponseInterface|null
     */
    public function __call($method, $args)
    {
        $valid_methods = [
            'get',
            'getAsync',
            'delete',
            'deleteAsync',
            'head',
            'headAsync',
            'options',
            'optionsAsync',
            'patch',
            'patchAsync',
            'post',
            'postAsync',
            'put',
            'putAsync'
        ];

        if ( ! in_array($method, $valid_methods))
        {
            return NULL;
        }

        array_unshift($args, strtoupper($method));
        return call_user_func_array([$this->client, 'request'], $args);
    }
}