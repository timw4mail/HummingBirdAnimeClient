/**
 * Javascript for editing manga, if logged in
 */
(($, AnimeClient) => {

	'use strict';

	$('.manga.list').on('click', '.edit_buttons button', function(e) {
		let this_sel = $(this);
		let parent_sel = $(this).closest("article");
		let manga_id = parent_sel.attr("id").replace("manga-", "");
		let type = this_sel.is(".plus_one_chapter") ? 'chapter' : 'volume';
		let completed = parseInt(parent_sel.find(`.${type}s_read`).text(), 10);
		let total = parseInt(parent_sel.find(`.${type}_count`).text(), 10);
		let manga_name = parent_sel.find('.name').text();

		if (isNaN(completed)) {
			completed = 0;
		}

		let data = {
			id: manga_id
		};

		// Update the total count
		data[type + "s_read"] = ++completed;

		$.ajax({
			data: data,
			dataType: 'json',
			type: 'POST',
			mimeType: 'application/json',
			url: AnimeClient.url('/manga/update'),
			success: (res, status) => {
				parent_sel.find(`.${type}s_read`).text(completed);
				AnimeClient.showMessage('success', `Sucessfully updated ${manga_name}`);
				AnimeClient.scrollToTop();
			},
			error: (xhr, errorType, error) => {
				console.error(error);
				AnimeClient.showMessage('error', `Failed to updated ${manga_name}`);
				AnimeClient.scrollToTop();
			}
		});
	});

})(Zepto, AnimeClient);