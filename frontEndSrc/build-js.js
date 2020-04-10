import compiler from '@ampproject/rollup-plugin-closure-compiler';

const plugins = [
	compiler({
		assumeFunctionWrapper: true,
		compilationLevel: 'WHITESPACE_ONLY', //'ADVANCED',
		createSourceMap: true,
		env: 'BROWSER',
		languageIn: 'ECMASCRIPT_2018',
		languageOut: 'ES3'
	})
];

const defaultOutput = {
	format: 'iife',
	sourcemap: true,
}

const nonModules = [{
	input: './js/anon.js',
	output: {
		...defaultOutput,
		file: '../public/js/anon.min.js',
		sourcemapFile: '../public/js/anon.min.js.map',
	},
	plugins,
}, {
	input: './js/index.js',
	output: {
		...defaultOutput,
		file: '../public/js/scripts.min.js',
		sourcemapFile: '../public/js/scripts.min.js.map',
	},
	plugins,
}, {
	input: './js/base/sort-tables.js',
	output: {
		...defaultOutput,
		file: '../public/js/tables.min.js',
		sourcemapFile: '../public/js/tables.min.js.map',
	},
	plugins,
}];

const moduleOutput = {
	format: 'es',
	sourcemap: false,
}

let modules = [{
	input: './js/anon.js',
	output: {
		...moduleOutput,
		file: '../public/es/anon.js',
	},
}, {
	input: './js/index.js',
	output: {
		...moduleOutput,
		file: '../public/es/scripts.js',
	},
}];

// Return the config array for rollup
export default [
	...nonModules,
	...modules,
];