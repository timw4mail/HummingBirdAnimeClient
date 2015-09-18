<?php

namespace Aviat\Ion\View;

use Aura\Html\HelperLocatorFactory;

use Aviat\Ion\View\HttpView;
use Aviat\Ion\Di\ContainerInterface;

class HtmlView extends HttpView {

	protected $helper;

	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->helper = (new HelperLocatorFactory)->newInstance();
	}

	/**
	 * Response mime type
	 *
	 * @var string
	 */
	protected $contentType = 'text/html';

	/**
	 * Render a basic html Template
	 *
	 * @param string $path
	 * @param array $data
	 * @return string
	 */
	public function render_template($path, $data)
	{
		$buffer = "";

		$data['helper'] = $this->helper;
		$data['escape'] = $this->helper->escape();

		ob_start();
		extract($data);
		include $path;
		$buffer = ob_get_contents();
		ob_end_clean();

		return $buffer;
	}
}
// End of HtmlView.php