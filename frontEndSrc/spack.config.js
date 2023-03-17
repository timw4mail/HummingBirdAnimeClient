const { config } = require("@swc/core/spack");

module.exports = config({
	entry: {
		'scripts.min': __dirname + '/js/index.js',
		'tables.min': __dirname + '/js/base/sort-tables.js',
	},
	output: {
		path: '../public/js',
	},
	options: {
		jsc: {
			parser: {
				syntax: "ecmascript",
				jsx: false,
			},
			target: 'es2016',
			loose: false,
		},
		minify: true,
		sourceMaps: false,
		isModule: true,
	}
});