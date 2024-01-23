<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion;

use Aviat\Ion\Exception\ImageCreationException;

use GdImage;

/**
 * A wrapper around GD functions to create images
 *
 * @property false|GdImage|null $img
 */
class ImageBuilder
{
	private GDImage|FALSE|NULL $_img;
	private int $fontSize = 10;

	private function __construct(private int $width = 200, private int $height = 200)
	{
		$this->_img = imagecreatetruecolor($this->width, $this->height);
	}

	public function __destruct()
	{
		$this->cleanup();
	}

	private function getImg(): GdImage
	{
		if ($this->_img instanceof GdImage)
		{
			return $this->_img;
		}

		throw new ImageCreationException('Invalid GD object');
	}

	public static function new(int $width = 200, int $height = 200): self
	{
		$i = new self($width, $height);
		if ($i->_img === FALSE)
		{
			throw new ImageCreationException('Could not create image object');
		}

		return $i;
	}

	public function setFontSize(int $size): self
	{
		$this->fontSize = $size;

		return $this;
	}

	public function enableAlphaBlending(bool $enable): self
	{
		$ab = imagealphablending($this->getImg(), $enable);
		if ( ! $ab)
		{
			throw new ImageCreationException('Failed to toggle image alpha blending');
		}

		return $this;
	}

	public function addCenteredText(string $text, int $red, int $green, int $blue, int $alpha = -1): self
	{
		// Create the font color
		$textColor = ($alpha > -1)
			? imagecolorallocatealpha($this->getImg(), $red, $green, $blue, $alpha)
			: imagecolorallocate($this->getImg(), $red, $green, $blue);
		if ($textColor === FALSE)
		{
			throw new ImageCreationException('Could not create image text color');
		}

		// Generate placeholder text
		$fontWidth = imagefontwidth($this->fontSize);
		$fontHeight = imagefontheight($this->fontSize);
		$length = strlen($text);
		$textWidth = $length * $fontWidth;
		$fxPos = (int) ceil((imagesx($this->getImg()) - $textWidth) / 2);
		$fyPos = (int) ceil((imagesy($this->getImg()) - $fontHeight) / 2);

		// Add the image text
		imagestring($this->getImg(), $this->fontSize, $fxPos, $fyPos, $text, $textColor);

		return $this;
	}

	public function addBackgroundColor(int $red, int $green, int $blue, int $alpha = -1): self
	{
		$fillColor = ($alpha > -1)
			? imagecolorallocatealpha($this->getImg(), $red, $green, $blue, $alpha)
			: imagecolorallocate($this->getImg(), $red, $green, $blue);

		if ($fillColor === FALSE)
		{
			throw new ImageCreationException('Failed to create image fill color');
		}

		$hasFilled = imagefill($this->getImg(), 0, 0, $fillColor);
		if ($hasFilled === FALSE)
		{
			throw new ImageCreationException('Failed to add background color to image');
		}

		return $this;
	}

	public function savePng(string $savePath, bool $saveAlpha = TRUE): bool
	{
		$setAlpha = imagesavealpha($this->getImg(), $saveAlpha);
		if ($setAlpha === FALSE)
		{
			throw new ImageCreationException('Failed to set image save alpha flag');
		}

		return imagepng($this->getImg(), $savePath, 9);
	}

	public function saveWebp(string $savePath): bool
	{
		return imagewebp($this->getImg(), $savePath);
	}

	public function saveJpg(string $savePath): bool
	{
		return imagejpeg($this->getImg(), $savePath);
	}

	public function saveGif(string $savePath): bool
	{
		return imagegif($this->getImg(), $savePath);
	}

	public function cleanup(): void
	{
		$cleaned = FALSE;

		if ($this->getImg() instanceof GdImage)
		{
			$cleaned = imagedestroy($this->getImg());
		}

		if ($cleaned === FALSE)
		{
			throw new ImageCreationException('Failed to clean up image resource');
		}
	}
}
