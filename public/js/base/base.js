var AnimeClient = (function(w) {

	'use strict';

	const slice = Array.prototype.slice;

	return {
		/**
		 * Placeholder function
		 */
		noop: () => {},
		/**
		 * DOM selector
		 *
		 * @param {string} selector - The dom selector string
		 * @param {object} context
		 * @return {array} - arrau of dom elements
		 */
		$(selector, context) {
			if (typeof selector != "string" || selector === undefined) {
				return selector;
			}

			context = (context != null && context.nodeType === 1)
				? context
				: document;

			let elements = [];
			if (selector.match(/^#([\w]+$)/)) {
				elements.push(document.getElementById(selector.split('#')[1]));
			} else {
				elements = slice.apply(context.querySelectorAll(selector));
			}

			return elements;
		},
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

			let sel = AnimeClient.$('.message')[0];
			if (sel !== undefined) {
				sel.innerHTML = template;
				sel.removeAttribute('hidden');
			} else {
				AnimeClient.$('header')[0].insertAdjacentHTML('beforeend', template);
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
})(window);