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
			target: 'es2018',
			loose: false,
			// keepClassNames: true,
			// preserveAllComments: true,
			minify: {
				compress: {
					unused: true,
				},
				mangle: true,
				format: {
					comments: false,
				}
			}
		},
		minify: true,
		sourceMaps: true,
		isModule: true,
	}
});