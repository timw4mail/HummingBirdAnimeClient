/**
 * Script for optimizing css
 */
const fs = require('fs');
const postcss = require('postcss');
const atImport = require('postcss-import');
const cssNext = require('postcss-preset-env');
const cssNano = require('cssnano');

const css = fs.readFileSync('css/src/all.css', 'utf-8');
const darkCss = fs.readFileSync('css/src/dark-override.css', 'utf-8');

const minOptions = {
	autoprefixer: false,
	colormin: false,
	minifyFontValues: false,
	options: {
		sourcemap: false
	}
};

const processOptions = {
	browser: '> 0.5%',
	features: {
		'custom-properties': true,
	},
	stage: 0,
};

(async () => {
	// Basic theme
	const light = await postcss()
			.use(atImport())
			.use(cssNext(processOptions))
			.use(cssNano(minOptions))
			.process(css, {
				from: 'css/src/all.css',
				to: 'css/app.min.css',
			});
	fs.writeFileSync('css/app.min.css', light);

	// Dark theme
	const dark = await postcss()
		.use(atImport())
		.use(cssNext(processOptions))
		.use(cssNano(minOptions))
		.process(darkCss, {
			from: 'css/dark-override.css',
			to: 'css/dark.min.css',
		});
	fs.writeFileSync('css/dark.min.css', dark);

	const autoDarkCss = `${light} @media (prefers-color-scheme: dark) { ${dark} }`
	fs.writeFileSync('css/dark-auto.min.css', autoDarkCss)

})();