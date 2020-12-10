<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aura\Html\HelperLocator;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;

/**
 * Helper object to manage form generation, especially for config editing
 */
final class FormGenerator {
	/**
	 * Html generation helper
	 *
	 * @var HelperLocator
	 */
	private HelperLocator $helper;

	/**
	 * FormGenerator constructor.
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	private function __construct(ContainerInterface $container)
	{
		$this->helper = $container->get('html-helper');
	}

	/**
	 * Create a new FormGenerator
	 *
	 * @param ContainerInterface $container
	 * @return $this
	 */
	public static function new(ContainerInterface $container): self
	{
		try
		{
			return new static($container);
		}
		catch (\Throwable $e)
		{
			dump($e);
			die();
		}
	}

	/**
	 * Generate the html structure of the form
	 *
	 * @param string $name
	 * @param array $form
	 * @return string
	 */
	public function generate(string $name, array $form): string
	{
		$type = $form['type'];
		$display = $form['display'] ?? TRUE;
		$value = $form['value'] ?? '';

		if ($display === FALSE)
		{
			return (string)$this->helper->input([
				'type' => 'hidden',
				'name' => $name,
				'value' => $value,
			]);
		}

		$params = [
			'name' => $name,
			'value' => $value,
			'attribs' => [
				'id' => $name,
			],
		];

		switch($type)
		{
			case 'boolean':
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

			default:
				break;
		}

		foreach (['readonly', 'disabled'] as $key)
		{
			if (array_key_exists($key, $form) && $form[$key] !== FALSE)
			{
				$params['attribs'][$key] = $form[$key];
			}
		}

		return (string)$this->helper->input($params);
	}
}