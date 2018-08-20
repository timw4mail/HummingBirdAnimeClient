self.addEventListener('install', event => {
	console.log('Worker installed');

	event.waitUntil(
		caches.open('hummingbird-anime-client')
			.then(cache => cache.addAll([
				'public/images/icons/favicon.ico',
				'public/css/app.min.css',
				'public/js/index.min.js',
				'public/js/index-authed.min.js',
				'public/js/tables.min.js',
			]))
	);
});

self.addEventListener('fetch', event => {

	event.respondWith(
		caches.match(event.request).then(response => {
			if (response !== undefined) {
				// Return cached version
				return response;
			}

			return fetch(event.request).then(response => {
				const clone = response.clone();

				caches.open('hummingbird-anime-client').then(cache => {
					cache.put(event.request, clone);
				});

				return response;
			});
		})
	);
});