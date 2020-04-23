/**
 * Script for optimizing css
 */
const fs = require('fs');
const postcss = require('postcss');
const atImport = require('postcss-import');
const cssNext = require('postcss-preset-env');
const cssNano = require('cssnano');

const lightCss = fs.readFileSync('css/light.css', 'utf-8');
const darkCss = fs.readFileSync('css/src/dark-override.css', 'utf-8');
const fullDarkCss = fs.readFileSync('css/dark.css', 'utf-8');

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

try {
	(async () => {
		// Basic theme
		const lightMin = await postcss()
			.use(atImport())
			.use(cssNext(processOptions))
			.use(cssNano(minOptions))
			.process(lightCss, {
				from: 'css/light.css',
				to: '/public/css/light.min.css',
			}).catch(console.error);
		fs.writeFileSync('../public/css/light.min.css', lightMin.css);

		// Dark theme
		const darkFullMin = await postcss()
			.use(atImport())
			.use(cssNext(processOptions))
			.use(cssNano(minOptions))
			.process(fullDarkCss, {
				from: 'css/dark.css',
				to: '/public/css/dark.min.css',
			});
		fs.writeFileSync('../public/css/dark.min.css', darkFullMin.css);

		// Dark override
		const darkMin = await postcss()
			.use(atImport())
			.use(cssNext(processOptions))
			.use(cssNano(minOptions))
			.process(darkCss, {
				from: 'css/dark-override.css',
				to: '/public/css/dark.min.css',
			}).catch(console.error);
		const autoDarkCss = `${lightMin} @media (prefers-color-scheme: dark) { ${darkMin.css} }`
		fs.writeFileSync('../public/css/auto.min.css', autoDarkCss)

	})();
} catch (e) {
	console.error(e)
}
