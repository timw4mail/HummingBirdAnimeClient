/**
 * Javascript for editing manga, if logged in
 */
(($, AnimeClient, w) => {

	'use strict';

	$('body.manga.list').on('click', '.edit_buttons button', function(e) {
		let this_sel = $(this);
		let parent_sel = $(this).closest("article");
		let manga_id = parent_sel.attr("id").replace("manga-", "");
		let type = this_sel.is(".plus_one_chapter") ? 'chapter' : 'volume';
		let completed = parseInt(parent_sel.find(`.${type}s_read`).text(), 10);
		let total = parseInt(parent_sel.find(`.${type}_count`).text(), 10);

		if (isNaN(completed))
		{
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
			method: 'POST',
			mimeType: 'application/json',
			url: AnimeClient.url('/manga/update'),
		}).done((res) => {
			parent_sel.find(`.${type}s_read`).text(completed);
			AnimeClient.showMessage('success', `Sucessfully updated ${res.body.manga[0].romaji_title}`);

			// scroll to top
			w.scroll(0,0);
		}).fail(() => {
			AnimeClient.showMessage('error', `Failed to updated ${res.body.manga[0].romaji_title}`);
		});
	});

})(jQuery, AnimeClient, window);