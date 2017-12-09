<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use function Amp\Promise\wait;

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\API\{HummingbirdClient, JsonAPI};
use Aviat\Ion\View\HtmlView;

/**
 * Controller for handling routes that don't fit elsewhere
 */
class Index extends BaseController {

	/**
	 * Purges the API cache
	 *
	 * @return void
	 */
	public function clearCache()
	{
		$this->cache->clear();
		$this->outputHTML('blank', [
			'title' => 'Cache cleared'
		], NULL, 200);
	}

	/**
	 * Show the login form
	 *
	 * @param string $status
	 * @return void
	 */
	public function login(string $status = '')
	{
		$message = '';

		$view = new HtmlView($this->container);

		if ($status !== '')
		{
			$message = $this->showMessage($view, 'error', $status);
		}

		// Set the redirect url
		$this->setSessionRedirect();

		$this->outputHTML('login', [
			'title' => 'Api login',
			'message' => $message
		], $view);
	}

	/**
	 * Attempt login authentication
	 *
	 * @return void
	 */
	public function loginAction()
	{
		$auth = $this->container->get('auth');
		$post = $this->request->getParsedBody();
		if ($auth->authenticate($post['password']))
		{
			$this->sessionRedirect();
			return;
		}

		$this->setFlashMessage('Invalid username or password.');
		$this->redirect($this->url->generate('login'), 303);
	}

	/**
	 * Deauthorize the current user
	 *
	 * @return void
	 */
	public function logout()
	{
		$auth = $this->container->get('auth');
		$auth->logout();

		$this->redirectToDefaultRoute();
	}

	/**
	 * Show the user profile page
	 *
	 * @return void
	 */
	public function me()
	{
		$username = $this->config->get(['kitsu_username']);
		$model = $this->container->get('kitsu-model');
		$data = $model->getUserData($username);
		$orgData = JsonAPI::organizeData($data)[0];
		$rels = $orgData['relationships'] ?? [];
		$favorites = array_key_exists('favorites', $rels) ? $rels['favorites'] : [];
		
		
		$this->outputHTML('me', [
			'title' => 'About ' . $this->config->get('whose_list'),
			'data' => $orgData,
			'attributes' => $orgData['attributes'],
			'relationships' => $rels,
			'favorites' => $this->organizeFavorites($favorites),
		]);
	}

	/**
	 * Get image covers from kitsu
	 *
	 * @return void
	 */
	public function images($type, $file)
	{
		$kitsuUrl = 'https://media.kitsu.io/';
		list($id, $ext) = explode('.', basename($file));
		switch ($type)
		{
			case 'anime':
				$kitsuUrl .= "anime/poster_images/{$id}/small.{$ext}";
			break;

			case 'avatars':
				$kitsuUrl .= "users/avatars/{$id}/original.{$ext}";
			break;

			case 'manga':
				$kitsuUrl .= "manga/poster_images/{$id}/small.{$ext}";
			break;

			case 'characters':
				$kitsuUrl .= "characters/images/{$id}/original.{$ext}";
			break;

			default:
				$this->notFound();
				return;
		}

		$promise = (new HummingbirdClient)->request($kitsuUrl);
		$response = wait($promise);
		$data = wait($response->getBody());

		$baseSavePath = $this->config->get('img_cache_path');
		file_put_contents("{$baseSavePath}/{$type}/{$id}.{$ext}", $data);
		header('Content-type: ' . $response->getHeader('content-type')[0]);
		echo $data;
	}

	private function organizeFavorites(array $rawfavorites): array
	{
		// return $rawfavorites;
		$output = [];

		unset($rawfavorites['data']);

		foreach($rawfavorites as $item)
		{
			$rank = $item['attributes']['favRank'];
			foreach($item['relationships']['item'] as $key => $fav)
			{
				$output[$key] = $output[$key] ?? [];
				foreach ($fav as $id => $data)
				{
					$output[$key][$rank] = array_merge(['id' => $id], $data['attributes']);
				}
			}

			ksort($output[$key]);
		}

		return $output;
	}
}