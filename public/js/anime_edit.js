/**
 * Javascript for editing anime, if logged in
 */
(function($, undefined){

	if (CONTROLLER !== "anime") return;

	// Action to increment episode count
	$(".media button.plus_one").on("click", function(e) {
		e.stopPropagation();

		var this_sel = $(this);
		var parent_sel = $(this).closest("article");
		var self = this;

		var watched_count = parseInt(parent_sel.find('.completed_number').text(), 10);
		var total_count = parseInt(parent_sel.find('.total_number').text(), 10);
		var title = parent_sel.find('.name a').text();

		// Setup the update data
		var data = {
			id: this_sel.parent('article').attr('id').replace('a-', ''),
			increment_episodes: true
		};

		// If the episode count is 0, and incremented,
		// change status to currently watching
		if (isNaN(watched_count) || watched_count === 0)
		{
			data.status = "currently-watching";
		}

		// If you increment at the last episode, mark as completed
		if (( ! isNaN(watched_count)) && (watched_count + 1) === total_count)
		{
			delete data.increment_episodes;
			data.status = "completed";
		}

		// okay, lets actually make some changes!
		$.post(BASE_URL + 'update', data, function(res) {
			if (res.status === 'completed')
			{
				parent_sel.hide();
			}

			add_message('success', "Sucessfully updated " + title);
			parent_sel.find('.completed_number').text(++watched_count);
		});
	});

}(jQuery));