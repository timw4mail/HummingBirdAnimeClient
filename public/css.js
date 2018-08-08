/**
 * Script for optimizing css
 */
const fs = require('fs');
const postcss = require('postcss');
const atImport = require('postcss-import');
const cssNext = require('postcss-cssnext');
const cssNano = require('cssnano');

const css = fs.readFileSync('css/base.css', 'utf-8');

postcss()
	.use(atImport())
	.use(cssNext())
	.use(cssNano({
		autoprefixer: false,
		colormin: false,
		minifyFontValues: false,
		options: {
			sourcemap: false
		}
	}))
	.process(css, {
		from: 'css/base.css',
		to: 'css/app.min.css'
	}).then(result => {
		fs.writeFileSync('css/app.min.css', result.css);
		fs.writeFileSync('css/app.min.css.map', result.map);
	});