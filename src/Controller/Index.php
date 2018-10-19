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
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\View\HtmlView;

/**
 * Controller for handling routes that don't fit elsewhere
 */
final class Index extends BaseController {
	/**
	 * @var \Aviat\API\Anilist\Model
	 */
	private $anilistModel;

	/**
	 * @var \Aviat\AnimeClient\Model\Settings
	 */
	private $settingsModel;

	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->anilistModel = $container->get('anilist-model');
		$this->settingsModel = $container->get('settings-model');
	}

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
		]);
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
	 * Redirect to Anilist to start Oauth flow
	 */
	public function anilistRedirect()
	{
		$redirectUrl = 'https://anilist.co/api/v2/oauth/authorize?' .
			http_build_query([
				'client_id' => $this->config->get(['anilist', 'client_id']),
				'redirect_uri' => $this->urlGenerator->url('/anilist-oauth'),
				'response_type' => 'code',
			]);

		$this->redirect($redirectUrl, 303);
	}

	/**
	 * Oauth callback for Anilist API
	 */
	public function anilistCallback()
	{
		$query = $this->request->getQueryParams();
		$authCode = $query['code'];
		$uri = $this->urlGenerator->url('/anilist-oauth');

		$authData = $this->anilistModel->authenticate($authCode, $uri);
		$settings = $this->settingsModel->getSettings();

		if (array_key_exists('error', $authData))
		{
			$this->errorPage(400, 'Error Linking Account', $authData['hint']);
			return;
		}

		// Update the override config file
		$anilistSettings = [
			'access_token' => $authData['access_token'],
			'access_token_expires' => (time() - 10) + $authData['expires_in'],
			'refresh_token' => $authData['refresh_token'],
		];

		$newSettings = $settings;
		$newSettings['anilist'] = array_merge($settings['anilist'], $anilistSettings);

		foreach($newSettings['config'] as $key => $value)
		{
			$newSettings[$key] = $value;
		}
		unset($newSettings['config']);

		$saved = $this->settingsModel->saveSettingsFile($newSettings);

		if ($saved)
		{
			$this->setFlashMessage('Linked Anilist Account', 'success');
		}
		else
		{
			$this->setFlashMessage('Error Linking Anilist Account', 'error');
		}

		$this->redirect($this->url->generate('settings'), 303);
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
	 * Show the user settings, if logged in
	 */
	public function settings()
	{
		$auth = $this->container->get('auth');
		$form = $this->settingsModel->getSettingsForm();

		$hasAnilistLogin = $this->config->has(['anilist','access_token']);

		$this->outputHTML('settings', [
			'anilistModel' => $this->anilistModel,
			'auth' => $auth,
			'form' => $form,
			'hasAnilistLogin' => $hasAnilistLogin,
			'config' => $this->config,
			'title' => $this->config->get('whose_list') . "'s Settings",
		]);
	}

	/**
	 * Attempt to save the user's settings
	 *
	 * @throws \Aura\Router\Exception\RouteNotFound
	 */
	public function settings_post()
	{
		$post = $this->request->getParsedBody();
		unset($post['settings-tabs']);

		// dump($post);
		$saved = $this->settingsModel->saveSettingsFile($post);

		if ($saved)
		{
			$this->setFlashMessage('Saved config settings.', 'success');
		}
		else
		{
			$this->setFlashMessage('Failed to save config file.', 'error');
		}

		$this->redirect($this->url->generate('settings'), 303);
	}

	/**
	 * Get image covers from kitsu
	 *
	 * @param string $type The category of image
	 * @param string $file The filename to look for
	 * @param bool $display Whether to output the image to the server
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \InvalidArgumentException
	 * @throws \TypeError
	 * @throws \Error
	 * @throws \Throwable
	 * @return void
	 */
	public function images(string $type, string $file, $display = TRUE): void
	{
		$kitsuUrl = 'https://media.kitsu.io/';
		$fileName = str_replace('-original', '', $file);
		[$id, $ext] = explode('.', basename($fileName));

		$typeMap = [
			'anime' => [
				'kitsuUrl' => "anime/poster_images/{$id}/medium.{$ext}",
				'width' => 220,
			],
			'avatars' => [
				'kitsuUrl' => "users/avatars/{$id}/original.{$ext}",
				'width' => null,
			],
			'characters' => [
				'kitsuUrl' => "characters/images/{$id}/original.{$ext}",
				'width' => 225,
			],
			'manga' => [
				'kitsuUrl' => "manga/poster_images/{$id}/medium.{$ext}",
				'width' => 220,
			],
			'people' => [
				'kitsuUrl' => "people/images/{$id}/original.{$ext}",
				'width' => null,
			],
		];

		if ( ! array_key_exists($type, $typeMap))
		{
			$this->notFound();
			return;
		}

		$kitsuUrl .= $typeMap[$type]['kitsuUrl'];
		$width = $typeMap[$type]['width'];

		$promise = (new HummingbirdClient)->request($kitsuUrl);
		$response = wait($promise);
		$data = wait($response->getBody());

		// echo "Fetching {$kitsuUrl}\n";

		$baseSavePath = $this->config->get('img_cache_path');
		$filePrefix = "{$baseSavePath}/{$type}/{$id}";

		[$origWidth] = getimagesizefromstring($data);
		$gdImg = imagecreatefromstring($data);
		$resizedImg = imagescale($gdImg, $width ?? $origWidth);

		// save the webp versions
		imagewebp($gdImg, "{$filePrefix}-original.webp");
		imagewebp($resizedImg, "{$filePrefix}.webp");

		// save the scaled jpeg file
		imagejpeg($resizedImg, "{$filePrefix}.jpg");

		imagedestroy($gdImg);
		imagedestroy($resizedImg);

		// And the original
		file_put_contents("{$filePrefix}-original.jpg", $data);

		if ($display)
		{
			$contentType = ($ext === 'webp')
				? "image/webp"
				: $response->getHeader('content-type')[0];

			$outputFile = (strpos($file, '-original') !== FALSE)
				? "{$filePrefix}-original.{$ext}"
				: "{$filePrefix}.{$ext}";

			header("Content-Type: {$contentType}");
			echo file_get_contents($outputFile);
		}
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