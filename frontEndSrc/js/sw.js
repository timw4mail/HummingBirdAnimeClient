// Start the service worker, if you can
if ('serviceWorker' in navigator) {
	navigator.serviceWorker.register('/sw.js').then(reg => {
		console.log('Service worker registered', reg.scope);
	}).catch(error => {
		console.error('Failed to register service worker', error);
	});
}