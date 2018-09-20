import _ from './base/AnimeClient.js';

// Wire up mal checkbox
_.on('main', 'change', '.big-check', (e) => {
	const id = e.target.id;
	_.$('.mal-check').forEach(el => {
		el.checked = false;
	})
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
					<input type="checkbox" class="mal-check" id="mal_${item.slug}" name="mal_id" value="${x.mal_id}" />
					<input type="radio" class="big-check" id="${item.slug}" name="id" value="${x.id}" />
					<label for="${item.slug}">
						<img src="/public/images/anime/${x.id}.jpg" alt="" width="220" />
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
					<input type="checkbox" id="mal_${item.slug}" name="mal_id" value="${x.mal_id}" />
					<input type="radio" class="big-check" id="${item.slug}" name="id" value="${x.id}" />
					<label for="${item.slug}">
						<img src="/public/images/manga/${x.id}.jpg" alt="" width="220" />
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