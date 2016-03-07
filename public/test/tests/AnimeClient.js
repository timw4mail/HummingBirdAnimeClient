var slice = Array.prototype.slice;

suite('AnimeClient methods exist', function () {
	test("AnimeClient exists", function () {
		expect(AnimeClient).to.be.ok;
	});
	['$', 'scrollToTop', 'showMessage', 'show', 'hide', 'closestParent', 'url', 'throttle', 'on', 'ajax', 'get'].forEach((method) => {
		test("AnimeClient." + method + ' exists.', function () {
			expect(AnimeClient[method]).to.be.ok;
		});
	});
});

suite('AnimeClient.$', function () {
	test('$ returns an array', function () {
		var matched = AnimeClient.$('div');
		expect(matched).to.be.an('array');
	});
	test('$ returns same element as "getElementById"', function () {
		var actual = AnimeClient.$('#mocha')[0];
		var expected = document.getElementById('mocha');

		expect(actual).to.equal(expected);
	});
	test('$ returns same elements as "getElementsByClassName"', function () {
		var actual = AnimeClient.$('.progress');
		var expected = slice.apply(document.getElementsByClassName('progress'));

		expect(actual).to.deep.equal(expected);
	});
	test('$ returns same elements as "getElementsByTagName"', function () {
		var actual = AnimeClient.$('ul');
		var expected = slice.apply(document.getElementsByTagName('ul'));

		expect(actual).to.deep.equal(expected);
	});
});

suite('AnimeClient.url', function () {
	test('url method has expected result', function () {
		let expected = `//${document.location.host}/path`;
		expect(AnimeClient.url('/path')).to.equal(expected);
	});
});

suite('AnimeClient.closestParent', function () {
	test('".grandChild" closest "section" is "#parentTest"', function () {
		let sel = AnimeClient.$('.grandChild')[0];
		let expected = document.getElementById('parentTest');

		expect(AnimeClient.closestParent(sel, 'section')).to.equal(expected);
	});
	test('".child" closest "article" is "#parentTest"', function () {
		let sel = AnimeClient.$('.child')[0];
		let expected = document.getElementById('parentTest');

		expect(AnimeClient.closestParent(sel, 'section')).to.equal(expected);
	});
});