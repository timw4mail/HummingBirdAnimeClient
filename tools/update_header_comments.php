<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Etc;

if ( ! function_exists('glob_recursive'))
{
	// Does not support flag GLOB_BRACE

	function glob_recursive(string $pattern, int $flags = 0): array
	{
		$files = glob($pattern, $flags);

		foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir)
		{
			$files = array_merge($files, glob_recursive($dir . '/' . basename($pattern), $flags));
		}

		return $files;
	}
}

function get_text_to_replace(array $tokens): string
{
	$output = '';

	// Tokens have the follow structure if arrays:
	// [0] => token type constant
	// [1] => raw syntax parsed to that token
	// [2] => line number
	foreach ($tokens as $token)
	{
		// Since we only care about opening docblocks,
		// bail out when we get to the namespace token
		if (is_array($token) && $token[0] === T_NAMESPACE)
		{
			break;
		}

		if (is_array($token))
		{
			$token = $token[1];
		}

		$output .= $token;
	}

	return $output;
}

function replace_file(string $file, string $template): void
{
	$source = file_get_contents($file);
	if ($source === FALSE || stripos($source, 'namespace') === FALSE)
	{
		return;
	}

	$tokens = token_get_all($source);
	$text_to_replace = get_text_to_replace($tokens);

	$header = file_get_contents(__DIR__ . $template);
	$new_text = "<?php declare(strict_types=1);\n{$header}";

	$new_source = str_replace($text_to_replace, $new_text, $source);
	file_put_contents($file, $new_source);
}

// ----------------------------------------------------------------------------

$files = array_filter(
	glob_recursive('*.php'),
	static fn (string $file) => ! (str_contains($file, '/vendor/') || str_contains($file, '/tmp/'))
);
array_walk($files, static fn (string $file) => replace_file($file, '/header_comment.txt'));

echo json_encode(array_values($files), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
printf("Successfully updated header comments in %d files\n", count($files));
