/**
 * Javascript for editing manga, if logged in
 */
((_) => {

	'use strict';

	_.on('.manga.list', 'click', '.edit_buttons button', (e) => {
		let thisSel = e.target;
		let parentSel = _.closestParent(e.target, 'article');
		let mangaId = parentSel.id.replace("manga-", "");
		let type = thisSel.classList.contains("plus_one_chapter") ? 'chapter' : 'volume';
		let completed = parseInt(_.$(`.${type}s_read`, parentSel)[0].textContent, 10);
		let total = parseInt(_.$(`.${type}_count`, parentSel)[0].textContent, 10);
		let mangaName = _.$('.name', parentSel)[0].textContent;

		if (isNaN(completed)) {
			completed = 0;
		}

		// Setup the update data
		let data = {
			id: mangaId,
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
		if (( ! isNaN(completed)) && (completed + 1) === total) {
			data.data.status = 'completed';
		}

		// Update the total count
		data.data.progress = ++completed;

		_.ajax(_.url('/manga/update'), {
			data,
			dataType: 'json',
			type: 'POST',
			mimeType: 'application/json',
			success: () => {
				if (data.data.status === 'completed') {
					_.hide(parentSel);
				}

				_.$(`.${type}s_read`, parentSel)[0].textContent = completed;
				_.showMessage('success', `Sucessfully updated ${mangaName}`);
				_.scrollToTop();
			},
			error: () => {
				_.showMessage('error', `Failed to updated ${mangaName}`);
				_.scrollToTop();
			}
		});
	});

})(AnimeClient);