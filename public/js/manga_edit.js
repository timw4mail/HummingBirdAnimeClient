/**
 * Javascript for editing manga, if logged in
 */
((_) => {

	'use strict';

	_.on('.manga.list', 'click', '.edit_buttons button', function(e) {
		let this_sel = this;
		let parent_sel = this.parentElement.parentElement;
		let manga_id = parent_sel.id.replace("manga-", "");
		let type = this_sel.classList.contains("plus_one_chapter") ? 'chapter' : 'volume';
		let completed = parseInt(_.$(`.${type}s_read`, parent_sel)[0].textContent, 10);
		let total = parseInt(_.$(`.${type}_count`, parent_sel)[0].textContent, 10);
		let manga_name = _.$('.name', parent_sel)[0].textContent;

		if (isNaN(completed)) {
			completed = 0;
		}

		let data = {
			id: manga_id
		};

		// Update the total count
		data[type + "s_read"] = ++completed;

		_.ajax(_.url('/manga/update'), {
			data: data,
			dataType: 'json',
			type: 'POST',
			mimeType: 'application/json',
			success: (res) => {
				_.$(`.${type}s_read`, parent_sel)[0].textContent = completed;
				_.showMessage('success', `Sucessfully updated ${manga_name}`);
				_.scrollToTop();
			},
			error: (xhr, errorType, error) => {
				console.error(error);
				_.showMessage('error', `Failed to updated ${manga_name}`);
				_.scrollToTop();
			}
		});
	});

})(AnimeClient);