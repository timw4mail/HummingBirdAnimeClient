import _ from './anime-client.js';

(() => {
	// Var is intentional
	var hidden = null;
	var visibilityChange = null;

	if (typeof document.hidden !== "undefined") {
		hidden = "hidden";
		visibilityChange = "visibilitychange";
	} else if (typeof document.msHidden !== "undefined") {
		hidden = "msHidden";
		visibilityChange = "msvisibilitychange";
	} else if (typeof document.webkitHidden !== "undefined") {
		hidden = "webkitHidden";
		visibilityChange = "webkitvisibilitychange";
	}

	function handleVisibilityChange() {
		// Check the user's session to see if they are currently logged-in
		// when the page becomes visible
		if ( ! document[hidden]) {
			_.get('/heartbeat', (beat) => {
				const status = JSON.parse(beat)

				// If the session is expired, immediately reload so that
				// you can't attempt to do an action that requires authentication
				if (status.hasAuth !== true) {
					document.removeEventListener(visibilityChange, handleVisibilityChange, false);
					location.reload();
				}
			});
		}
	}

	if (hidden === null) {
		console.info('Page visibility API not supported, JS session check will not work');
	} else {
		document.addEventListener(visibilityChange, handleVisibilityChange, false);
	}
})();