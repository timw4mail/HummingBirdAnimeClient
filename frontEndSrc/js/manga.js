import _ from './anime-client.js'
import { renderSearchResults } from './template-helpers.js'
import { getNestedProperty, hasNestedProperty } from "./fns";

const search = (query) => {
	_.show('.cssload-loader');
	return _.get(_.url('/manga/search'), { query }, (searchResults, status) => {
		searchResults = JSON.parse(searchResults);
		_.hide('.cssload-loader');
		_.$('#series-list')[ 0 ].innerHTML = renderSearchResults('manga', searchResults);
	});
};

if (_.hasElement('.manga #search')) {
	let prevRequest = null

	_.on('#search', 'input', _.throttle(250, (e) => {
		let query = encodeURIComponent(e.target.value);
		if (query === '') {
			return;
		}

		if (prevRequest !== null) {
			prevRequest.abort();
		}

		prevRequest = search(query);
	}));
}

/**
 * Javascript for editing manga, if logged in
 */
_.on('.manga.list', 'click', '.edit-buttons button', (e) => {
	let thisSel = e.target;
	let parentSel = _.closestParent(e.target, 'article');
	let type = thisSel.classList.contains('plus-one-chapter') ? 'chapter' : 'volume';
	let completed = parseInt(_.$(`.${type}s_read`, parentSel)[ 0 ].textContent, 10) || 0;
	let total = parseInt(_.$(`.${type}_count`, parentSel)[ 0 ].textContent, 10);
	let title = _.$('.name', parentSel)[ 0 ].textContent;

	if (isNaN(completed)) {
		completed = 0;
	}

	// Setup the update data
	let data = {
		id: parentSel.dataset.kitsuId,
		anilist_id: parentSel.dataset.anilistId,
		mal_id: parentSel.dataset.malId,
		data: {
			progress: completed
		}
	};

	const displayMessage = (type, message) => {
		_.hide('#loading-shadow');
		_.showMessage(type, `${message} ${title}.`);
		_.scrollToTop();
	}

	const showError = () => displayMessage('error', 'Failed to update');

	// If the episode count is 0, and incremented,
	// change status to currently reading
	if (isNaN(completed) || completed === 0) {
		data.data.status = 'CURRENT';
	}

	// If you increment at the last chapter, mark as completed
	if ((!isNaN(completed)) && (completed + 1) === total) {
		data.data.status = 'COMPLETED';
	}

	// Update the total count
	data.data.progress = ++completed;

	_.show('#loading-shadow');

	_.ajax(_.url('/manga/increment'), {
		data,
		dataType: 'json',
		type: 'POST',
		mimeType: 'application/json',
		success: (res) => {
			try {
				const resData = JSON.parse(res);

				// Do a rough sanity check for weird errors
				let updatedProgress = getNestedProperty(resData, 'data.libraryEntry.update.libraryEntry.progress');
				if (hasNestedProperty(resData, 'error') || updatedProgress !== data.data.progress) {
					showError();
					return;
				}

				// We've completed the series
				if (getNestedProperty(resData, 'data.libraryEntry.update.libraryEntry.status') === 'COMPLETED') {
					_.hide(parentSel);
					displayMessage('success', 'Completed')

					return;
				}

				// Just a normal update
				_.$(`.${type}s_read`, parentSel)[ 0 ].textContent = String(completed);
				displayMessage('success', 'Updated');

			} catch (_) {
				showError();
			}
		},
		error: showError,
	});
});