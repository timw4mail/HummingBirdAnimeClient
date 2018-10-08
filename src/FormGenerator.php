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

namespace Aviat\AnimeClient;

use Aviat\Ion\
{
	ArrayWrapper, StringWrapper
};
use Aviat\Ion\Di\ContainerInterface;

/**
 * Helper object to manage form generation, especially for config editing
 */
final class FormGenerator {
	use ArrayWrapper;
	use StringWrapper;

	/**
	 * Injection Container
	 * @var ContainerInterface $container
	 */
	protected $container;

	/**
	 * Html generation helper
	 *
	 * @var \Aura\Html\HelperLocator
	 */
	protected $helper;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->helper = $container->get('html-helper');
	}

	/**
	 * Generate the html structure of the form
	 *
	 * @param string $name
	 * @param array $form
	 * @return string
	 */
	public function generate(string $name, array $form)
	{
		$type = $form['type'];

		if ($form['display'] === FALSE)
		{
			return $this->helper->input([
				'type' => 'hidden',
				'name' => $name,
				'value' => $form['value'],
			]);
		}

		$params = [
			'name' => $name,
			'value' => $form['value'],
			'attribs' => [
				'id' => $name,
			],
		];

		switch($type)
		{
			case 'boolean':
				/* $params['type'] = 'checkbox';
				$params['attribs']['label'] = $form['description'];
				$params['attribs']['value'] = TRUE;
				$params['attribs']['value_unchecked'] = '0'; */

				$params['type'] = 'radio';
				$params['options'] = [
					'1' => 'Yes',
					'0' => 'No',
				];
				unset($params['attribs']['id']);
			break;

			case 'string':
				$params['type'] = 'text';
			break;

			case 'select':
				$params['type'] = 'select';
				$params['options'] = array_flip($form['options']);
			break;
		}

		foreach (['readonly', 'disabled'] as $key)
		{
			if ($form[$key] !== FALSE)
			{
				$params['attribs'][$key] = $form[$key];
			}
		}

		return $this->helper->input($params);
	}
}