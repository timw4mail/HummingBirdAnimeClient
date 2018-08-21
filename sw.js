const CACHE_NAME = 'hummingbird-anime-client';

async function fromCache (request) {
	const cache = await caches.open(CACHE_NAME);
	return await cache.match(request);
}

async function fromNetwork (request) {
	return await fetch(request);
}

async function update (request) {
	const cache = await caches.open(CACHE_NAME);
	const response = await fetch(request);

	if (request.url.includes('/public/images/')) {
		console.log('Saving to cache: ', request.url);
		await cache.put(request, response.clone());
	}

	return response;
}

function refresh (response) {
	return self.clients.matchAll().then(clients => {
		clients.forEach(client => {
			const message = {
				type: 'refresh',
				url: response.url,
				eTag: response.headers.get('ETag')
			};

			client.postMessage(JSON.stringify(message));
		})
	});
}

self.addEventListener('install', event => {
	console.log('Public Folder Worker installed');

	event.waitUntil(
		caches.open(CACHE_NAME)
			.then(cache => cache.addAll([
				'public/images/icons/favicon.ico',
				'public/images/streaming-logos/amazon.svg',
				'public/images/streaming-logos/crunchyroll.svg',
				'public/images/streaming-logos/daisuki.svg',
				'public/images/streaming-logos/funimation.svg',
				'public/images/streaming-logos/hidive.svg',
				'public/images/streaming-logos/hulu.svg',
				'public/images/streaming-logos/netflix.svg',
				'public/images/streaming-logos/tubitv.svg',
				'public/images/streaming-logos/viewster.svg',
			]))
	)
});

self.addEventListener('activate', event => {
	console.log('Public Folder Worker activated');
});

// Pull css, images, and javascript from cache
self.addEventListener('fetch', event => {
	fromCache(event.request).then(cached => {
		if (cached !== undefined) {
			event.respondWith(cached);
		} else {
			event.respondWith(fromNetwork(event.request));
		}
	});

	event.waitUntil(
		update(event.request).then(refresh)
	);
});