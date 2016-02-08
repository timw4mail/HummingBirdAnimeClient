describe("AnimeClient Base", () => {
	it("AnimeClient exists", () => {
		expect(AnimeClient).toBeDefined();
	});
	describe('AnimeClient methods exist', () => {
		['scrollToTop', 'showMessage', 'url', 'throttle', 'on'].forEach((method) => {
			it("AnimeClient." + method + ' exists.', () => {
				expect(AnimeClient[method]).toBeDefined();
			});
		});
	});
});

describe('AnimeClient.url', () => {

});

describe('AnimeClient.ajax', () => {

});