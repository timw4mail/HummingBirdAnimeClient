/**
 * Script for optimizing css
 */
const fs = require('fs');
const postcss = require('postcss');
const atImport = require('postcss-import');
const cssNext = require('postcss-cssnext');
const cssNano = require('cssnano');

const css = fs.readFileSync('css/all.css', 'utf-8');
const darkCss = fs.readFileSync('css/dark-override.css', 'utf-8');

// Basic theme
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
		from: 'css/all.css',
		to: 'css/app.min.css'
	}).then(result => {
	fs.writeFileSync('css/app.min.css', result.css);
});

// Dark theme
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
	.process(darkCss, {
		from: 'css/dark-override.css',
		to: 'css/dark.min.css'
	}).then(result => {
	fs.writeFileSync('css/dark.min.css', result.css);
});