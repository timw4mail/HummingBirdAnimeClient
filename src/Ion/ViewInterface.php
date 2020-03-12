<?php declare(strict_types=1);
/**
 * Ion
 *
 * Building blocks for web development
 *
 * PHP version 7.2
 *
 * @package     Ion
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.0.0
 * @link        https://git.timshomepage.net/aviat/ion
 */

namespace Aviat\Ion;

use Aviat\Ion\Exception\DoubleRenderException;

/**
 * View Interface abstracting a Response
 */
interface ViewInterface {
	/**
	 * Return rendered output as string. Renders the view,
	 * and any attempts to call again will result in a DoubleRenderException
	 *
	 * @throws DoubleRenderException
	 * @return string
	 */
	public function __toString(): string;

	/**
	 * Set the output string
	 *
	 * @param mixed $string
	 * @return ViewInterface
	 */
	public function setOutput($string): self;

	/**
	 * Append additional output.
	 *
	 * @param string $string
	 * @return ViewInterface
	 */
	public function appendOutput(string $string): self;

	/**
	 * Add an http header
	 *
	 * @param string $name
	 * @param string|string[] $value
	 * @return ViewInterface
	 */
	public function addHeader(string $name, $value): self;

	/**
	 * Get the current output as a string. Does not
	 * render view or send headers.
	 *
	 * @return string
	 */
	public function getOutput(): string;

	/**
	 * Send output to client. As it renders the view,
	 * any attempt to call again will result in a DoubleRenderException.
	 *
	 * @throws DoubleRenderException
	 * @return void
	 */
	public function send(): void;
}