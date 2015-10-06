<?php
/**
 * Base for base models
 */
namespace Aviat\AnimeClient;

use abeautifulsite\SimpleImage;
use Aviat\Ion\Di\ContainerInterface;

/**
 * Common base for all Models
 */
class Model {

	use \Aviat\Ion\StringWrapper;

	/**
	 * The global configuration object
	 * @var Config
	 */
	protected $config;

	/**
	 * The container object
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->config = $container->get('config');
	}

	/**
	 * Get the path of the cached version of the image. Create the cached image
	 * if the file does not already exist
	 *
	 * @codeCoverageIgnore
	 * @param string $api_path - The original image url
	 * @param string $series_slug - The part of the url with the series name, becomes the image name
	 * @param string $type - Anime or Manga, controls cache path
	 * @return string - the frontend path for the cached image
	 */
	public function get_cached_image($api_path, $series_slug, $type = "anime")
	{
		$api_path = str_replace("jjpg", "jpg", $api_path);
		$path_parts = explode('?', basename($api_path));
		$path = current($path_parts);
		$ext_parts = explode('.', $path);
		$ext = end($ext_parts);

		// Workaround for some broken extensions
		if ($ext == "jjpg") $ext = "jpg";

		// Failsafe for weird urls
		if (strlen($ext) > 3) return $api_path;

		$img_cache_path = $this->config->get('img_cache_path');
		$cached_image = "{$series_slug}.{$ext}";
		$cached_path = "{$img_cache_path}/{$type}/{$cached_image}";

		// Cache the file if it doesn't already exist
		if ( ! file_exists($cached_path))
		{
			/*if (ini_get('allow_url_fopen'))
			{
				copy($api_path, $cached_path);
			}
			else*/if (function_exists('curl_init'))
			{
				$ch = curl_init($api_path);
				$fp = fopen($cached_path, 'wb');
				curl_setopt_array($ch, [
					CURLOPT_FILE => $fp,
					CURLOPT_HEADER => 0
				]);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);
			}
			else
			{
				throw new DomainException("Couldn't cache images because they couldn't be downloaded.");
			}

			// Resize the image
			if ($type == 'anime')
			{
				$resize_width = 220;
				$resize_height = 319;
				$this->_resize($cached_path, $resize_width, $resize_height);
			}
		}

		return "/public/images/{$type}/{$cached_image}";
	}

	/**
	 * Resize an image
	 *
	 * @codeCoverageIgnore
	 * @param string $path
	 * @param string $width
	 * @param string $height
	 */
	private function _resize($path, $width, $height)
	{
		$img = new SimpleImage($path);
		$img->resize($width, $height)->save();
	}
}
// End of BaseModel.php