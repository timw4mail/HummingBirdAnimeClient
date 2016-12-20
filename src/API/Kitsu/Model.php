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

namespace Aviat\AnimeClient\API\Kitsu;

use Aviat\AnimeClient\Model\API;

/**
 * Kitsu API Model
 */
class Model extends API {
	
	const CLIENT_ID = 'dd031b32d2f56c990b1425efe6c42ad847e7fe3ab46bf1299f05ecd856bdb7dd';
	const CLIENT_SECRET = '54d7307928f63414defd96399fc31ba847961ceaecef3a5fd93144e960c0e151';
	
	/**
	 * Base url for Kitsu API
	 */
	protected $baseUrl = 'https://kitsu.io/api/edge/';

    /**
     * Default settings for Guzzle
     * @var array
     */
	protected $connectionDefaults = [];

    /**
     * Get the access token from the Kitsu API
     *
     * @param string $username
     * @param string $password
     * @return bool|string
     */
    public function authenticate(string $username, string $password)
    {
        $response = $this->post('https://kitsu.io/api/oauth/token', [
            'body' => http_build_query([
                'grant_type' => 'password',
                'username' => $username,
                'password' => $password,
                'client_id' => self::CLIENT_ID,
                'client_secret' => self::CLIENT_SECRET
            ])
        ]);

        $info = $response->getBody();

        if (array_key_exists('access_token', $info)) {
            // @TODO save token
            return true;
        }

        return false;
    }
}