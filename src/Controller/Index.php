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

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\API\JsonAPI;
use Aviat\Ion\View\HtmlView;

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
		$orgData = JsonAPI::organizeData($data);
		$this->outputHTML('me', [
			'title' => 'About' . $this->config->get('whose_list'),
			'data' => $orgData[0],
			'attributes' => $orgData[0]['attributes'],
			'relationships' => $orgData[0]['relationships'],
			'favorites' => $this->organizeFavorites($orgData[0]['relationships']['favorites']),
		]);
	}

	private function organizeFavorites(array $rawfavorites): array
	{
		// return $rawfavorites;
		$output = [];

		foreach($rawfavorites as $item)
		{
			$rank = $item['attributes']['favRank'];
			foreach($item['relationships']['item'] as $key => $fav)
			{
				$output[$key] = $output[$key] ?? [];
				foreach ($fav as $id => $data)
				{
					$output[$key][$rank] = $data['attributes'];
				}
			}

			ksort($output[$key]);
		}

		return $output;
	}
}