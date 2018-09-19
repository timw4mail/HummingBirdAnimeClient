import _ from './base/AnimeClient.js'
import { renderMangaSearchResults } from './template-helpers.js'

const search = (query) => {
	_.$('.cssload-loader')[ 0 ].removeAttribute('hidden');
	_.get(_.url('/manga/search'), { query }, (searchResults, status) => {
		searchResults = JSON.parse(searchResults);
		_.$('.cssload-loader')[ 0 ].setAttribute('hidden', 'hidden');
		_.$('#series_list')[ 0 ].innerHTML = renderMangaSearchResults(searchResults.data);
	});
};

if (_.hasElement('.manga #search')) {
	_.on('#search', 'keyup', _.throttle(250, (e) => {
		let query = encodeURIComponent(e.target.value);
		if (query === '') {
			return;
		}

		search(query);
	}));
}

/**
 * Javascript for editing manga, if logged in
 */
_.on('.manga.list', 'click', '.edit_buttons button', (e) => {
	let thisSel = e.target;
	let parentSel = _.closestParent(e.target, 'article');
	let type = thisSel.classList.contains('plus_one_chapter') ? 'chapter' : 'volume';
	let completed = parseInt(_.$(`.${type}s_read`, parentSel)[ 0 ].textContent, 10) || 0;
	let total = parseInt(_.$(`.${type}_count`, parentSel)[ 0 ].textContent, 10);
	let mangaName = _.$('.name', parentSel)[ 0 ].textContent;

	if (isNaN(completed)) {
		completed = 0;
	}

	// Setup the update data
	let data = {
		id: parentSel.dataset.kitsuId,
		mal_id: parentSel.dataset.malId,
		data: {
			progress: completed
		}
	};

	// If the episode count is 0, and incremented,
	// change status to currently reading
	if (isNaN(completed) || completed === 0) {
		data.data.status = 'current';
	}

	// If you increment at the last chapter, mark as completed
	if ((!isNaN(completed)) && (completed + 1) === total) {
		data.data.status = 'completed';
	}

	// Update the total count
	data.data.progress = ++completed;

	_.show(_.$('#loading-shadow')[ 0 ]);

	_.ajax(_.url('/manga/update'), {
		data,
		dataType: 'json',
		type: 'POST',
		mimeType: 'application/json',
		success: () => {
			if (data.data.status === 'completed') {
				_.hide(parentSel);
			}

			_.hide(_.$('#loading-shadow')[ 0 ]);

			_.$(`.${type}s_read`, parentSel)[ 0 ].textContent = completed;
			_.showMessage('success', `Successfully updated ${mangaName}`);
			_.scrollToTop();
		},
		error: () => {
			_.hide(_.$('#loading-shadow')[ 0 ]);
			_.showMessage('error', `Failed to update ${mangaName}`);
			_.scrollToTop();
		}
	});
});