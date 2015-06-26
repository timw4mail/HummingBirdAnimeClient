<?php
/**
 * Easy Min
 *
 * Simple minification for better website performance
 *
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Easy-Min
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * This is the config array for css files to concatenate and minify
 */
return [
	/*-----
			Css
					-----*/

	/*
		For each group create an array like so

		'my_group' => array(
			'path/to/css/file1.css',
			'path/to/css/file2.css'
		),
	*/
	'base' => [
		'marx.css',
		'base.css'
	]
];
// End of css_groups.php