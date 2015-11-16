/**
 * Javascript for editing manga, if logged in
 */
(function ($) {

	"use strict";

	if (CONTROLLER !== "manga") return;

	$(".edit_buttons button").on("click", function(e) {
		var this_sel = $(this);
		var parent_sel = $(this).closest("article");
		var manga_id = parent_sel.attr("id").replace("manga-", "");
		var type = this_sel.is(".plus_one_chapter") ? 'chapter' : 'volume';
		var completed = parseInt(parent_sel.find("." + type + "s_read").text(), 10);
		var total = parseInt(parent_sel.find("."+type+"_count").text(), 10);

		console.log(completed);
		console.log(total);

		if (isNaN(completed))
		{
			completed = 0;
		}

		var data = {
			id: manga_id
		};

		// Update the total count
		data[type + "s_read"] = ++completed;

		$.ajax({
			data: data,
			dataType: 'json',
			method: 'POST',
			mimeType: 'application/json',
			url: BASE_URL + CONTROLLER + '/update'
		}).done(function(res) {
			console.table(res);
			parent_sel.find("."+type+"s_read").text(completed);
			add_message('success', "Sucessfully updated " + res.manga[0].romaji_title);
		}).fail(function() {
			add_message('error', "Failed to updated " + res.manga[0].romaji_title);
		});
	});

}(jQuery));