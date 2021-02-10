<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion;

use Aviat\Ion\Type\StringType;

/**
 * Helper class for json convenience methods
 */
class Json {

	/**
	 * Encode data in json format
	 *
	 * @param mixed $data
	 * @param int   $options
	 * @param int   $depth
	 * @throws JsonException
	 * @return string
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
	 * @param string $filename
	 * @param mixed  $data
	 * @param int    $jsonOptions - Options to pass to json_encode
	 * @param int    $fileOptions - Options to pass to file_get_contents
	 * @throws JsonException
	 * @return bool
	 */
	public static function encodeFile(string $filename, mixed $data, int $jsonOptions = 0, int $fileOptions = 0): bool
	{
		$json = self::encode($data, $jsonOptions);

		$res = file_put_contents($filename, $json, $fileOptions);
		return $res !== FALSE;
	}

	/**
	 * Decode data from json
	 *
	 * @param string|null $json
	 * @param bool   $assoc
	 * @param int    $depth
	 * @param int    $options
	 * @throws JsonException
	 * @return mixed
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
	 * @param string $filename
	 * @param bool   $assoc
 	 * @param int    $depth
 	 * @param int    $options
	 * @throws JsonException
	 * @return mixed
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
	 * @param  string $string
	 * @throws \InvalidArgumentException
	 * @return boolean
	 */
	public static function isJson(string $string): bool
	{
		return StringType::create($string)->isJson();
	}

	/**
	 * Call the json error functions to check for errors encoding/decoding
	 *
	 * @throws JsonException
	 * @return void
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
			JSON_ERROR_UNSUPPORTED_TYPE => 'JSON_ERROR_UNSUPPORTED_TYPE'
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