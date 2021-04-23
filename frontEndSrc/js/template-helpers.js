import _ from './anime-client.js';

// Click on hidden MAL checkbox so
// that MAL id is passed
_.on('main', 'change', '.big-check', (e) => {
	const id = e.target.id;
	document.getElementById(`mal_${id}`).checked = true;
});

export function renderAnimeSearchResults (data) {
	return data.map(item => {
		const titles = item.titles.join('<br />');

		return `
			<article class="media search">
				<div class="name">
					<input type="radio" class="mal-check" id="mal_${item.slug}" name="mal_id" value="${item.mal_id}" />
					<input type="radio" class="big-check" id="${item.slug}" name="id" value="${item.id}" />
					<label for="${item.slug}">
						<picture width="220">
							<source srcset="/public/images/anime/${item.id}.webp" type="image/webp" />
							<source srcset="/public/images/anime/${item.id}.jpg" type="image/jpeg" />
							<img src="/public/images/anime/${item.id}.jpg" alt="" width="220" />
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
		`;
	}).join('');
}

export function renderMangaSearchResults (data) {
	return data.map(item => {
		const titles = item.titles.join('<br />');
		return `
			<article class="media search">
				<div class="name">
					<input type="radio" id="mal_${item.slug}" name="mal_id" value="${item.mal_id}" />
					<input type="radio" class="big-check" id="${item.slug}" name="id" value="${item.id}" />
					<label for="${item.slug}">
						<picture width="220">
							<source srcset="/public/images/manga/${item.id}.webp" type="image/webp" />
							<source srcset="/public/images/manga/${item.id}.jpg" type="image/jpeg" />
							<img src="/public/images/manga/${item.id}.jpg" alt="" width="220" />
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
		`;
	}).join('');
}