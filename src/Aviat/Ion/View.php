<?php

namespace Aviat\Ion;

use Aviat\Ion\Di\ContainerInterface;

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
	 * @var S
	 */
	protected $output;

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
		$this->output();
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