const AnimeClient = (function($) {

	'use strict';

	return {
		/**
		 * Display a message box
		 *
		 * @param  {String} type - message type: info, error, success
		 * @param  {String} message - the message itself
		 * @return {void}
		 */
		showMessage(type, message) {
			let template = `
				<div class="message ${type}">
					<span class="icon"></span>
					${message}
					<span class="close"></span>
				</div>`;

			if ($(".message").length > 0)
			{
				$(".message").replaceWith(template);
				$(".message").show();
			}
			else
			{
				$("header").append(template);
			}
		},
		/**
		 * Generate a full url from a relative path
		 *
		 * @param  {String} path - url path
		 * @return {String} - full url
		 */
		url(path) {
			let uri = `//${document.location.host}`;
			uri += (path.charAt(0) === '/') ? path : `/${path}`;

			return uri;
		},
	};

})(jQuery);