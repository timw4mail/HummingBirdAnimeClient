<?php
/**
 * Ion
 *
 * Building blocks for web development
 *
 * @package     Ion
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @license     MIT
 */

namespace Aviat\Ion;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Type\StringType;

/**
 * Base view response class
 */
abstract class View {

	use Di\ContainerAware;
	use \Aviat\Ion\StringWrapper;

	/**
	 * HTTP response Object
	 *
	 * @var Aura\Web\Response
	 */
	protected $response;

	/**
	 * Response mime type
	 *
	 * @var string
	 */
	protected $contentType = '';

	/**
	 * String of response to be output
	 *
	 * @var StringType
	 */
	protected $output;

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
	 * Return rendered output
	 *
	 * @return string
	 */
	public function __toString()
	{
		$this->hasRendered = TRUE;
		return $this->getOutput();
	}

	/**
	 * Set the output string
	 *
	 * @param string $string
	 * @return View
	 */
	public function setOutput($string)
	{
		$this->output = $this->string($string);

		return $this;
	}

	/**
	 * Append additional output
	 *
	 * @param string $string
	 * @return View
	 */
	public function appendOutput($string)
	{
		$this->output = $this->string($this->output)->append($string);

		return $this;
	}

	/**
	 * Get the current output string
	 *
	 * @return string
	 */
	public function getOutput()
	{
		return $this->string($this->output)->__toString();
	}

	/**
	 * Send output to client
	 */
	public function send()
	{
		$this->hasRendered = TRUE;
		$this->output();
	}

	/**
	 * Send the appropriate response
	 *
	 * @return void
	 */
	protected function output()
	{
		$content =& $this->response->content;
		$content->set($this->output);
		$content->setType($this->contentType);
		$content->setCharset('utf-8');
	}
}
// End of View.php