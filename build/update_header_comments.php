<?php
declare(strict_types=1);

$file_patterns = [
	'app/appConf/*.php',
	'app/bootstrap.php',
	'migrations/*.php',
	'src/**/*.php',
	'src/*.php',
	'tests/**/*.php',
	'tests/*.php',
	'index.php',
	'Robofile.php'
];

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
	foreach($tokens as $token)
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

function get_tokens(string $source): array
{
	return token_get_all($source);
}

function replace_files(array $files, string $template): void
{
	print_r($files);
	foreach ($files as $file)
	{
		$source = file_get_contents($file);
		if ($source === FALSE)
		{
			continue;
		}

		if (stripos($source, 'namespace') === FALSE)
		{
			continue;
		}

		$tokens = get_tokens($source);
		$text_to_replace = get_text_to_replace($tokens);

		$header = file_get_contents(__DIR__ . $template);
		$new_text = "<?php declare(strict_types=1);\n{$header}";

		$new_source = str_replace($text_to_replace, $new_text, $source);
		file_put_contents($file, $new_source);
	}
}

foreach ($file_patterns as $glob)
{
	$files = glob_recursive($glob);
	replace_files($files, '/header_comment.txt');
}

echo "Successfully updated headers \n";
