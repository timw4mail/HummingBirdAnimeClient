/**
 * Javascript for editing anime, if logged in
 */
((_) => {

	'use strict';

	// Action to increment episode count
	_.on('body.anime.list', 'click', '.plus_one', function() {
		let parent_sel = _.closestParent(this, 'article');
		let watched_count = parseInt(_.$('.completed_number', parent_sel)[0].textContent, 10);
		let total_count = parseInt(_.$('.total_number', parent_sel)[0].textContent, 10);
		let title = _.$('.name a', parent_sel)[0].textContent;

		// Setup the update data
		let data = {
			id: parent_sel.id,
			data: {
				progress: watched_count + 1
			}
		};

		// If the episode count is 0, and incremented,
		// change status to currently watching
		if (isNaN(watched_count) || watched_count === 0) {
			data.data.status = 'current';
		}

		// If you increment at the last episode, mark as completed
		if (( ! isNaN(watched_count)) && (watched_count + 1) == total_count) {
			data.data.status = 'completed';
		}

		// okay, lets actually make some changes!
		_.ajax(_.url('/anime/update'), {
			data: data,
			dataType: 'json',
			type: 'POST',
			success: () => {
				if (data.data.status == 'completed') {
					_.hide(parent_sel);
				}

				_.showMessage('success', `Sucessfully updated ${title}`);
				_.$('.completed_number', parent_sel)[0].textContent = ++watched_count;
				_.scrollToTop();
			},
			error: (xhr, errorType, error) => {
				console.error(error);
				_.showMessage('error', `Failed to update ${title}. `);
				_.scrollToTop();
			}
		});
	});

})(AnimeClient);