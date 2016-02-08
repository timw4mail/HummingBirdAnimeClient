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
	it('url method has expected result', () => {
		let expected = `//${document.location.host}/path`;
		expect(AnimeClient.url('/path')).toBe(expected);
	});
});

describe('AnimeClient.ajax', () => {

});