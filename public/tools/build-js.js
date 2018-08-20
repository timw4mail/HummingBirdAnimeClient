import closure from 'rollup-plugin-closure-compiler-js';

const plugins = [
	closure({
		assumeFunctionWrapper: true,
		compilationLevel: 'SIMPLE', //'WHITESPACE_ONLY', //'ADVANCED',
		createSourceMap: true,
		env: 'BROWSER',
		languageIn: 'ES6',
		languageOut: 'ES5'
	})
];

const defaultOutput = {
	format: 'iife',
	sourcemap: true,
}


export default [{
	input: './js/src/index.js',
	output: {
		...defaultOutput,
		file: './js/scripts.min.js',
		sourcemapFile: './js/scripts.min.js.map',
	},
	plugins,
}, {
	input: './js/src/index-authed.js',
	output: {
		...defaultOutput,
		file: './js/scripts-authed.min.js',
		sourcemapFile: './js/scripts-authed.min.js.map',
	},
	plugins,
}, {
	input: './js/src/base/sort_tables.js',
	output: {
		...defaultOutput,
		file: './js/tables.min.js',
		sourcemapFile: './js/tables.min.js.map',
	},
	plugins,
}];

