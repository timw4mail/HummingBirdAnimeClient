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

use Aviat\Ion\Type\StringType;

/**
 * Helper class for json convenience methods
 */
class Json {

	/**
	 * Encode data in json format
	 *
	 * @param mixed $data
	 * @param int $options
	 * @param int $depth
	 * @return string
	 */
	public static function encode($data, $options = 0, $depth = 512)
	{
		$json = json_encode($data, $options, $depth);
		self::check_json_error();
		return $json;
	}

	/**
	 * Encode data in json format and save to a file
	 *
	 * @param string $filename
	 * @param mixed $data
	 * @param int $json_options - Options to pass to json_encode
	 * @param int $file_options - Options to pass to file_get_contents
	 * @return int
	 */
	public static function encodeFile($filename, $data, $json_options = 0, $file_options = 0)
	{
		$json = self::encode($data, $json_options);
		return file_put_contents($filename, $json, $file_options);
	}

	/**
	 * Decode data from json
	 *
	 * @param string $json
	 * @param bool $assoc
	 * @param int $depth
	 * @param int $options
	 * @return mixed
	 */
	public static function decode($json, $assoc = TRUE, $depth = 512, $options = 0)
	{
		// Don't try to decode null
		if (empty($json))
		{
			return NULL;
		}

		// cast json to string so that streams from guzzle are correctly decoded
		$data = json_decode((string) $json, $assoc, $depth, $options);

		self::check_json_error();
		return $data;
	}

	/**
	 * Decode json data loaded from the passed filename
	 *
	 * @param string $filename
	 * @param bool $assoc
 	 * @param int $depth
 	 * @param int $options
	 * @return mixed
	 */
	public static function decodeFile($filename, $assoc = TRUE, $depth = 512, $options = 0)
	{
		$json = file_get_contents($filename);
		return self::decode($json, $assoc, $depth, $options);
	}

	/**
	 * Determines whether a string is valid json
	 *
	 * @param  string  $string
	 * @return boolean
	 */
	public static function isJson($string)
	{
		return StringType::create($string)->isJson();
	}

	/**
	 * Call the json error functions to check for errors encoding/decoding
	 *
	 * @throws JsonException
	 */
	protected static function check_json_error()
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

		if (\JSON_ERROR_NONE !== $error)
		{
			throw new JsonException("{$constant_map[$error]} - {$message}", $error);
		}
	}
}
// End of JSON.php