<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

use DOMDocument, DOMNode, DOMNodelist;

/**
 * XML <=> PHP Array codec
 */
class XML {

	/**
	 * XML representation of the data
	 *
	 * @var string
	 */
	private $xml;

	/**
	 * PHP array version of the data
	 *
	 * @var array
	 */
	private $data;

	/**
	 * XML constructor
	 */
	public function __construct(string $xml = '', array $data = [])
	{
		$this->setXML($xml)->setData($data);
	}

	/**
	 * Serialize the data to an xml string
	 */
	public function __toString(): string
	{
		return static::toXML($this->getData());
	}

	/**
	 * Get the data parsed from the XML
	 *
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * Set the data to create xml from
	 *
	 * @param array $data
	 * @return $this
	 */
	public function setData(array $data): self
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * Get the xml created from the data
	 *
	 * @return string
	 */
	public function getXML(): string
	{
		return $this->xml;
	}

	/**
	 * Set the xml to parse the data from
	 *
	 * @param string $xml
	 * @return $this
	 */
	public function setXML(string $xml): self
	{
		$this->xml = $xml;
		return $this;
	}

	/**
	 * Parse an xml document string to a php array
	 *
	 * @param string $xml
	 * @return array
	 */
	public static function toArray(string $xml): array
	{
		$data = [];

		// Get rid of unimportant text nodes by removing
		// whitespace characters from between xml tags,
		// except for the xml declaration tag, Which looks
		// something like:
		/* <?xml version="1.0" encoding="UTF-8"?> */
		$xml = preg_replace('/([^\?])>\s+</', '$1><', $xml);

		$dom = new DOMDocument();
		$dom->loadXML($xml);
		$root = $dom->documentElement;

		$data[$root->tagName] = [];

		if ($root->hasChildNodes())
		{
			static::childNodesToArray($data[$root->tagName], $root->childNodes);
		}

		return $data;
	}

	/**
	 * Transform the array into XML
	 *
	 * @param array $data
	 * @return string
	 */
	public static function toXML(array $data): string
	{
		$dom = new DOMDocument();
		$dom->encoding = 'UTF-8';

		static::arrayPropertiesToXmlNodes($dom, $dom, $data);

		return $dom->saveXML();
	}

	/**
	 * Parse the xml document string to a php array
	 *
	 * @return array
	 */
	public function parse(): array
	{
		$xml = $this->getXML();
		$data = static::toArray($xml);
		return $this->setData($data)->getData();
	}

	/**
	 * Transform the array into XML
	 *
	 * @return string
	 */
	public function createXML(): string
	{
		return static::toXML($this->getData());
	}

	/**
	 * Recursively create array structure based on xml structure
	 *
	 * @param array &$root A reference to the current array location
	 * @param DOMNodeList $nodeList The current NodeList object
	 * @return void
	 */
	private static function childNodesToArray(array &$root, DOMNodelist $nodeList)
	{
		$length = $nodeList->length;
		for ($i = 0; $i < $length; $i++)
		{
			$el = $nodeList->item($i);
			if (is_a($el->childNodes->item(0), 'DomText') || ( ! $el->hasChildNodes()))
			{
				$root[$el->nodeName] = $el->textContent;
			}
			else
			{
				$root[$el->nodeName] = [];
				static::childNodesToArray($root[$el->nodeName], $el->childNodes);
			}
		}
	}

	/**
	 * Recursively create xml nodes from array properties
	 *
	 * @param DOMDocument $dom The current DOM object
	 * @param DOMNode $parent The parent element to append children to
	 * @param array $data The data for the current node
	 * @return void
	 */
	private static function arrayPropertiesToXmlNodes(DOMDocument &$dom, DOMNode &$parent, array $data)
	{
		foreach($data as $key => $props)
		{
			$node = $dom->createElement($key);
			if (is_array($props))
			{
				static::arrayPropertiesToXmlNodes($dom, $node, $props);
			}
			else
			{
				$tNode = $dom->createTextNode((string)$props);
				$node->appendChild($tNode);
			}

			$parent->appendChild($node);
		}
	}
}