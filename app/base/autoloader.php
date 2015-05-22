<?php

function anime_autoloader($class) {
	$dirs = ["base", "controllers", "models"];

	foreach($dirs as $dir)
	{
		$file = realpath(__DIR__ . "/../{$dir}/{$class}.php");
		if (file_exists($file))
		{
			require_once $file;
			return;
		}
	}
}

spl_autoload_register('anime_autoloader');