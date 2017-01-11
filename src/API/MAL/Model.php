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
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */namespace Aviat\AnimeClient\API\Kitsu;

use Aviat\AnimeClient\Model\API;

/**
 * MyAnimeList API Model
 */
class Model extends API {

    /**
     * Base url for Kitsu API
     */
    protected $baseUrl = 'https://myanimelist.net/api/';

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
        $response = $this->post('account/', [
            'body' => http_build_query([
                'grant_type' => 'password',
                'username' => $username,
                'password' => $password
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