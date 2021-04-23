module.exports = {
	entry: {
		'scripts.min': __dirname + '/js/index.js',
		'tables.min': __dirname + '/js/base/sort-tables.js',
	},
	output: {
		path: '../public/js',
	},
	options: {
		jsc: {
			target: 'es3',
			loose: true,
		},
		minify: true,
		module: {
			type: 'es6'
		}
	}
}