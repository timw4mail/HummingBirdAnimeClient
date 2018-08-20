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


export default [{
	input: './js/src/index.js',
	output: {
		file: './js/scripts.min.js',
		format: 'iife',
		sourcemap: true,
		sourcemapFile: './js/scripts.min.js.map',
	},
	plugins,
}, {
	input: './js/src/index-authed.js',
	output: {
		file: './js/scripts-authed.min.js',
		format: 'iife',
		sourcemap: true,
		sourcemapFile: './js/scripts-authed.min.js.map',
	},
	plugins,
}, {
	input: './js/src/base/sort_tables.js',
	output: {
		file: './js/tables.min.js',
		format: 'iife',
		sourcemap: true,
		sourcemapFile: './js/tables.min.js.map',
	},
	plugins,
}];

