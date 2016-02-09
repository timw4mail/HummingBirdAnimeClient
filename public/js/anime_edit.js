/**
 * Javascript for editing anime, if logged in
 */
((_) => {

	'use strict';

	// Action to increment episode count
	_.on('body.anime.list', 'click', '.plus_one', function(e) {
		let this_sel = this;
		let parent_sel = this.parentElement;

		let watched_count = parseInt(_.$('.completed_number', parent_sel)[0].textContent, 10);
		let total_count = parseInt(_.$('.total_number', parent_sel)[0].textContent, 10);
		let title = _.$('.name a', parent_sel)[0].textContent;

		// Setup the update data
		let data = {
			id: parent_sel.id,
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
		_.ajax(_.url('/anime/update'), {
			data: data,
			dataType: 'json',
			type: 'POST',
			mimeType: 'application/json',
			success: (res) => {
				if (res.status === 'completed') {
					this.parentElement.addAttribute('hidden', 'hidden');
				}

				_.showMessage('success', `Sucessfully updated ${title}`);
				_.$('.completed_number', parent_sel)[0].textContent = ++watched_count;
				_.scrollToTop();
			},
			error: (xhr, errorType, error) => {
				console.error(error);
				_.showMessage('error', `Failed to updated ${title}. `);
				_.scrollToTop();
			}
		});
	});

})(AnimeClient);