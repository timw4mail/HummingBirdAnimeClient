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

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\Ion\Di\ContainerInterface;

/**
 * Controller for user settings
 */
final class Settings extends BaseController {
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
	 * Show the user settings, if logged in
	 */
	public function index()
	{
		$auth = $this->container->get('auth');
		$form = $this->settingsModel->getSettingsForm();

		$hasAnilistLogin = $this->config->has(['anilist', 'access_token']);

		$this->outputHTML('settings/settings', [
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
	public function update()
	{
		$post = $this->request->getParsedBody();
		unset($post['settings-tabs']);

		// dump($post);
		$saved = $this->settingsModel->saveSettingsFile($post);

		if ($saved)
		{
			$this->setFlashMessage('Saved config settings.', 'success');
		} else
		{
			$this->setFlashMessage('Failed to save config file.', 'error');
		}

		$this->redirect($this->url->generate('settings'), 303);
	}
}