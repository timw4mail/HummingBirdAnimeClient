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

use Aviat\Ion\Type\StringType;
use InvalidArgumentException;

/**
 * Helper class for json convenience methods
 */
class Json
{
	/**
	 * Encode data in json format
	 *
	 * @throws JsonException
	 */
	public static function encode(mixed $data, int $options = 0, int $depth = 512): string
	{
		$json = json_encode($data, $options, $depth);
		self::check_json_error();

		return ($json !== FALSE) ? $json : '';
	}

	/**
	 * Encode data in json format and save to a file
	 *
	 * @param int $jsonOptions - Options to pass to json_encode
	 * @param int $fileOptions - Options to pass to file_get_contents
	 * @throws JsonException
	 */
	public static function encodeFile(string $filename, mixed $data, int $jsonOptions = 0, int $fileOptions = 0): int
	{
		$json = self::encode($data, $jsonOptions);

		$res = file_put_contents($filename, $json, $fileOptions);

		return ($res !== FALSE) ? $res : 0;
	}

	/**
	 * Decode data from json
	 *
	 * @throws JsonException
	 */
	public static function decode(?string $json, bool $assoc = TRUE, int $depth = 512, int $options = 0): mixed
	{
		// Don't try to decode null
		if ($json === NULL)
		{
			return NULL;
		}

		$data = json_decode($json, $assoc, $depth, $options);

		self::check_json_error();

		return $data;
	}

	/**
	 * Decode json data loaded from the passed filename
	 *
	 * @throws JsonException
	 */
	public static function decodeFile(string $filename, bool $assoc = TRUE, int $depth = 512, int $options = 0): mixed
	{
		$rawJson = file_get_contents($filename);
		$json = ($rawJson !== FALSE) ? $rawJson : '';

		return self::decode($json, $assoc, $depth, $options);
	}

	/**
	 * Determines whether a string is valid json
	 *
	 * @throws InvalidArgumentException
	 */
	public static function isJson(string $string): bool
	{
		return StringType::create($string)->isJson();
	}

	/**
	 * Call the json error functions to check for errors encoding/decoding
	 *
	 * @throws JsonException
	 */
	protected static function check_json_error(): void
	{
		$constant_map = [
			JSON_ERROR_NONE => 'JSON_ERROR_NONE',
			JSON_ERROR_DEPTH => 'JSON_ERROR_DEPTH',
			JSON_ERROR_STATE_MISMATCH => 'JSON_ERROR_STATE_MISMATCH',
			JSON_ERROR_CTRL_CHAR => 'JSON_ERROR_CTRL_CHAR',
			JSON_ERROR_SYNTAX => 'JSON_ERROR_SYNTAX',
			JSON_ERROR_UTF8 => 'JSON_ERROR_UTF8',
			JSON_ERROR_RECURSION => 'JSON_ERROR_RECURSION',
			JSON_ERROR_INF_OR_NAN => 'JSON_ERROR_INF_OR_NAN',
			JSON_ERROR_UNSUPPORTED_TYPE => 'JSON_ERROR_UNSUPPORTED_TYPE',
		];

		$error = json_last_error();
		$message = json_last_error_msg();

		if (JSON_ERROR_NONE !== $error)
		{
			throw new JsonException("{$constant_map[$error]} - {$message}", $error);
		}
	}
}

// End of JSON.php
