/**
 * Javascript for editing anime, if logged in
 */
(($, AnimeClient) => {

	'use strict';

	// Action to increment episode count
	$('body.anime.list').on('click', '.plus_one', function(e) {
		let self = this;
		let this_sel = $(this);
		let parent_sel = $(this).closest('article, td');

		let watched_count = parseInt(parent_sel.find('.completed_number').text(), 10);
		let total_count = parseInt(parent_sel.find('.total_number').text(), 10);
		let title = parent_sel.find('.name a').text();

		// Setup the update data
		let data = {
			id: this_sel.parent('article, td').attr('id'),
			increment_episodes: true
		};

		// If the episode count is 0, and incremented,
		// change status to currently watching
		if (isNaN(watched_count) || watched_count === 0) {
			data.status = 'currently-watching';
		}

		// If you increment at the last episode, mark as completed
		if (( ! isNaN(watched_count)) && (watched_count + 1) === total_count) {
			delete data.increment_episodes;
			data.status = 'completed';
		}

		// okay, lets actually make some changes!
		$.ajax({
			data: data,
			dataType: 'json',
			type: 'POST',
			mimeType: 'application/json',
			url: AnimeClient.url('/anime/update'),
			success: (res) => {
				if (res.status === 'completed') {
					$(this).closest('article, tr').hide();
				}

				AnimeClient.showMessage('success', `Sucessfully updated ${title}`);
				parent_sel.find('.completed_number').text(++watched_count);
				AnimeClient.scrollToTop();
			},
			error: (xhr, errorType, error) => {
				console.error(error);
				AnimeClient.showMessage('error', `Failed to updated ${title}. `);
				AnimeClient.scrollToTop();
			}
		});
	});

})(Zepto, AnimeClient);