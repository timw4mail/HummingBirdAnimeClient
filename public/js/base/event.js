AnimeClient = (function (ac) {
	'use strict';

	function add(sel, event, listener) {
		// Recurse!
		if (! event.match(/^([\w\-]+)$/)) {
			event.split(' ').forEach((evt) => {
				add(sel, evt, listener);
			});
		}

		sel.addEventListener(event, listener, false);
	}
	function delegate(sel, target, event, listener) {
		// Attach the listener to the parent
		add(sel, event, (e) => {
			// Get live version of the target selector
			ac.$(target, sel).forEach((element) => {
				if(e.target == element) {
					listener.call(element, e);
					e.stopPropagation();
				}
			});
		});
	}

	ac.on = function (sel, event, target, listener) {
		if (arguments.length === 3) {
			listener = target;
			ac.$(sel).forEach((el) => {
				add(el, event, listener);
			});
		} else {
			ac.$(sel).forEach((el) => {
				delegate(el, target, event, listener);
			});
		}
	}

	return ac;
})(AnimeClient);