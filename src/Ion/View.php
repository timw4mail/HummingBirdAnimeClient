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

use Psr\Http\Message\ResponseInterface;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Exception\DoubleRenderException;

/**
 * Base view response class
 */
abstract class View
	// partially
	implements ViewInterface {

	use Di\ContainerAware;
	use StringWrapper;

	/**
	 * HTTP response Object
	 *
	 * @var ResponseInterface
	 */
	public $response;

	/**
	 * If the view has sent output via
	 * __toString or send method
	 *
	 * @var boolean
	 */
	protected $hasRendered = FALSE;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 * @throws Di\Exception\ContainerException
	 * @throws Di\Exception\NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
		$this->response = $container->get('response');
	}

	/**
	 * Send output to client
	 */
	public function __destruct()
	{
		if ( ! $this->hasRendered)
		{
			$this->send();
		}
	}

	/**
	 * Return rendered output as string. Renders the view,
	 * and any attempts to call again will result in a DoubleRenderException
	 *
	 * @throws DoubleRenderException
	 * @return string
	 */
	public function __toString(): string
	{
		if ($this->hasRendered)
		{
			throw new DoubleRenderException();
		}
		$this->hasRendered = TRUE;
		return $this->getOutput();
	}

	/**
	 * Add an http header
	 *
	 * @param string $name
	 * @param string|string[] $value
	 * @throws \InvalidArgumentException
	 * @return ViewInterface
	 */
	public function addHeader(string $name, $value): ViewInterface
	{
		$this->response = $this->response->withHeader($name, $value);
		return $this;
	}

	/**
	 * Set the output string
	 *
	 * @param mixed $string
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @return ViewInterface
	 */
	public function setOutput($string): ViewInterface
	{
		$this->response->getBody()->write($string);

		return $this;
	}

	/**
	 * Append additional output.
	 *
	 * @param string $string
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @return ViewInterface
	 */
	public function appendOutput(string $string): ViewInterface
	{
		return $this->setOutput($string);
	}

	/**
	 * Get the current output as a string. Does not
	 * render view or send headers.
	 *
	 * @return string
	 */
	public function getOutput(): string
	{
		return (string)$this->response->getBody();
	}
}
// End of View.php