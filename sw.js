const CACHE_NAME = 'hummingbird-anime-client';

async function fromCache (request) {
	const cache = await caches.open(CACHE_NAME);
	return await cache.match(request);
}

async function updateCache (request) {
	const cache = await caches.open(CACHE_NAME);
	const response = await fetch(request);

	if (request.url.includes('/public/images/')) {
		await cache.put(request, response.clone());
	}

	return response;
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

self.addEventListener('activate', () => {
	console.info('Public Folder Worker activated');
});

// Pull css, images, and javascript from cache
self.addEventListener('fetch', event => {
	// Only cache things with a file extension,
	// Ignore other requests
	if ( ! event.request.url.includes('/public/')) {
		return;
	}

	fromCache(event.request).then(cached => {
		if (cached !== undefined) {
			event.respondWith(cached);
		} else {
			event.respondWith(updateCache(event.request));
		}
	});
});