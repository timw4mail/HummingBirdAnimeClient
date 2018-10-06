import _ from './base/AnimeClient.js';

// Click on hidden MAL checkbox so
// that MAL id is passed
_.on('main', 'change', '.big-check', (e) => {
	const id = e.target.id;
	document.getElementById(`mal_${id}`).checked = true;
});

export function renderAnimeSearchResults (data) {
	const results = [];

	data.forEach(x => {
		const item = x.attributes;
		const titles = item.titles.reduce((prev, current) => {
			return prev + `${current}<br />`;
		}, []);

		results.push(`
			<article class="media search">
				<div class="name">
					<input type="radio" class="mal-check" id="mal_${item.slug}" name="mal_id" value="${x.mal_id}" />
					<input type="radio" class="big-check" id="${item.slug}" name="id" value="${x.id}" />
					<label for="${item.slug}">
						<picture width="220">
							<source srcset="/public/images/anime/${x.id}.webp" type="image/webp" />
							<source srcset="/public/images/anime/${x.id}.jpg" type="image/jpeg" />
							<img src="/public/images/anime/${x.id}.jpg" alt="" width="220" />
						</picture>
						
						<span class="name">
							${item.canonicalTitle}<br />
							<small>${titles}</small>
						</span>
					</label>
				</div>
				<div class="table">
					<div class="row">
						<span class="edit">
							<a class="bracketed" href="/anime/details/${item.slug}">Info Page</a>
						</span>
					</div>
				</div>
			</article>
		`);
	});

	return results.join('');
}

export function renderMangaSearchResults (data) {
	const results = [];

	data.forEach(x => {
		const item = x.attributes;
		const titles = item.titles.reduce((prev, current) => {
			return prev + `${current}<br />`;
		}, []);

		results.push(`
			<article class="media search">
				<div class="name">
					<input type="radio" id="mal_${item.slug}" name="mal_id" value="${x.mal_id}" />
					<input type="radio" class="big-check" id="${item.slug}" name="id" value="${x.id}" />
					<label for="${item.slug}">
						<picture width="220">
							<source srcset="/public/images/manga/${x.id}.webp" type="image/webp" />
							<source srcset="/public/images/manga/${x.id}.jpg" type="image/jpeg" />
							<img src="/public/images/manga/${x.id}.jpg" alt="" width="220" />
						</picture>
						<span class="name">
							${item.canonicalTitle}<br />
							<small>${titles}</small>
						</span>
					</label>
				</div>
				<div class="table">
					<div class="row">
						<span class="edit">
							<a class="bracketed" href="/manga/details/${item.slug}">Info Page</a>
						</span>
					</div>
				</div>
			</article>
		`);
	});

	return results.join('');
}