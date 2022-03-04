<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion;

use Aviat\Ion\Exception\DoubleRenderException;

/**
 * View Interface abstracting a Response
 */
interface ViewInterface
{
	/**
	 * Return rendered output as string. Renders the view,
	 * and any attempts to call again will result in a DoubleRenderException
	 *
	 * @throws DoubleRenderException
	 */
	public function __toString(): string;

	/**
	 * Set the output string
	 */
	public function setOutput(mixed $string): self;

	/**
	 * Append additional output.
	 */
	public function appendOutput(string $string): self;

	/**
	 * Add an http header
	 *
	 * @param string|string[] $value
	 */
	public function addHeader(string $name, array|string $value): self;

	/**
	 * Get the current output as a string. Does not
	 * render view or send headers.
	 */
	public function getOutput(): string;

	/**
	 * Send output to client. As it renders the view,
	 * any attempt to call again will result in a DoubleRenderException.
	 *
	 * @throws DoubleRenderException
	 */
	public function send(): void;
}
