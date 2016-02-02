const AnimeClient = (function($, w) {

	'use strict';

	return {
		/**
		 * Scroll to the top of the Page
		 *
		 * @return {void}
		 */
		scrollToTop() {
			w.scroll(0,0);
		},
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

			if ($(".message").length > 0) {
				$(".message").replaceWith(template);
				$(".message").show();
			} else {
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
		/**
		 * Throttle execution of a function
		 *
		 * @see https://remysharp.com/2010/07/21/throttling-function-calls
		 * @see https://jsfiddle.net/jonathansampson/m7G64/
		 * @param {Number} interval - the minimum throttle time in ms
		 * @param {Function} fn - the function to throttle
		 * @param {Object} scope - the 'this' object for the function
		 * @return {void}
		 */
		throttle(interval, fn, scope) {
			var wait = false;
			return function () {
				var context = scope || this;
				var args = arguments;

				if ( ! wait) {
					fn.apply(context, args);
					wait = true;
					setTimeout(function() {
						wait = false;
					}, interval);
				}
			};
		},
	};
})(Zepto, window);