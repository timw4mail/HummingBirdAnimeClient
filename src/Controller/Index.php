<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.1
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
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
final class Index extends BaseController {

	/**
	 * Purges the API cache
	 *
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function clearCache()
	{
		$this->cache->clear();
		$this->outputHTML('blank', [
			'title' => 'Cache cleared'
		]);
	}

	/**
	 * Show the login form
	 *
	 * @param string $status
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
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
	 * Redirect to Anilist to start Oauth flow
	 */
	public function anilistRedirect()
	{
		$redirectUrl = 'https://anilist.co/api/v2/oauth/authorize?' .
			http_build_query([
				'client_id' => $this->config->get(['anilist', 'client_id']),
				'response_type' => 'code',
			]);

		$this->redirect($redirectUrl, 301);
	}

	/**
	 * Oauth callback for Anilist API
	 */
	public function anilistCallback()
	{
		$this->outputHTML('blank', [
			'title' => 'Oauth!'
		]);
	}

	/**
	 * Attempt login authentication
	 *
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \Aura\Router\Exception\RouteNotFound
	 * @throws \InvalidArgumentException
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
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
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
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
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
	 * Show the user settings, if logged in
	 */
	public function settings()
	{
		$auth = $this->container->get('auth');
		$this->outputHTML('settings', [
			'auth' => $auth,
			'config' => $this->config,
			'title' => $this->config->get('whose_list') . "'s Settings",
		]);
	}

	public function settings_post()
	{
		$auth = $this->container->get('auth');
		$this->outputHTML('settings', [
			'auth' => $auth,
			'config' => $this->config,
			'title' => $this->config->get('whose_list') . "'s Settings",
		]);
	}

	/**
	 * Get image covers from kitsu
	 *
	 * @param string $type The category of image
	 * @param string $file The filename to look for
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
	 * @throws \TypeError
	 * @throws \Error
	 * @throws \Throwable
	 * @return void
	 */
	public function images(string $type, string $file): void
	{
		$kitsuUrl = 'https://media.kitsu.io/';
		[$id, $ext] = explode('.', basename($file));
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

	/**
	 * Reorganize favorites data to be more useful
	 *
	 * @param array $rawfavorites
	 * @return array
	 */
	private function organizeFavorites(array $rawfavorites): array
	{
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