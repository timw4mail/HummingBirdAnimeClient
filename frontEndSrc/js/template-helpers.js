import _ from './anime-client.js';

// Click on hidden MAL checkbox so
// that MAL id is passed
_.on('main', 'change', '.big-check', (e) => {
	const id = e.target.id;
	document.getElementById(`mal_${id}`).checked = true;
});

/**
 * On search results with an existing library entry, this shows that fact, with an edit link for the existing
 * library entry
 *
 * @param {'anime'|'manga'} type
 * @param {Object} item
 * @param isCollection
 * @returns {String}
 */
function renderEditLink (type, item, isCollection = false) {
	if (isCollection || item.libraryEntry === null) {
		return '';
	}

	return `
		<div class="row">
			<span class="edit"><big>[ Already in List ]</big></span>
		</div>
		<div class="row">
			<span class="edit">
				<a class="bracketed" href="/${type}/edit/${item.libraryEntry.id}/${item.libraryEntry.status}">Edit</a>
			</span>
		</div>
		<div class="row"><span class="edit">&nbsp;</span></div>
	`
}

/**
 * Show the search results for a media item
 *
 * @param {'anime'|'manga'} type
 * @param {Object} data
 * @param {boolean} isCollection
 * @returns {String}
 */
export function renderSearchResults (type, data, isCollection = false) {
	return data.map(item => {
		const titles = item.titles.join('<br />');
		let disabled = item.libraryEntry !== null ? 'disabled' : '';
		const editLink = renderEditLink(type, item, isCollection);

		if (isCollection) {
			disabled = '';
		}

		return `
			<article class="media search ${disabled}">
				<div class="name">
					<input type="radio" class="mal-check" id="mal_${item.slug}" name="mal_id" value="${item.mal_id}" ${disabled} />
					<input type="radio" class="big-check" id="${item.slug}" name="id" value="${item.id}" ${disabled} />
					<label for="${item.slug}">
						<picture width="220">
							<source srcset="/public/images/${type}/${item.id}.webp" type="image/webp" />
							<source srcset="/public/images/${type}/${item.id}.jpg" type="image/jpeg" />
							<img src="/public/images/${type}/${item.id}.jpg" alt="" width="220" />
						</picture>
						<span class="name">
							${item.canonicalTitle}<br />
							<small>${titles}</small>
						</span>
					</label>
				</div>
				<div class="table">
					${editLink}
					<div class="row">
						<span class="edit">
							<a class="bracketed" href="/${type}/details/${item.slug}">Info Page</a>
						</span>
					</div>
				</div>
			</article>
		`;
	}).join('');
}