suite('AnimeClient.ajax', function () {
	'use strict';

	test('AnimeClient.get method', function (done) {
		AnimeClient.get('ajax.php', function (res) {
			expect(res).to.be.ok;
			done();
		});
	});
	test('GET', function (done) {
		AnimeClient.ajax('ajax.php', {
			success: function (res) {
				expect(res).to.be.ok;
				done();
			},
			error: function (err) {
				expect.fail;
				done();
			}
		});
	});
	test('POST', function (done) {
		AnimeClient.ajax('ajax.php', {
			type: 'POST',
			success: function (res) {
				expect(res).to.be.ok;
				done();
			},
			error: function (err) {
				expect.fail;
				done();
			}
		});
	});
	test('PUT', function (done) {
		AnimeClient.ajax('ajax.php', {
			type: 'PUT',
			success: function (res) {
				expect(res).to.be.ok;
				done();
			},
			error: function (err) {
				expect.fail;
				done();
			}
		});
	});
	test('DELETE', function (done) {
		AnimeClient.ajax('ajax.php', {
			type: 'DELETE',
			success: function (res) {
				expect(res).to.be.ok;
				done();
			},
			error: function (err) {
				expect.fail;
				done();
			}
		});
	});
	test('POST with data', function (done) {
		var expected = '{"foo":"data"}';

		AnimeClient.ajax('ajax.php?data', {
			data: {foo:'data'},
			type: 'POST',
			success: function (res) {
				expect(res).to.be.equal(expected);
				done();
			},
			error: function (err) {
				expect.fail;
				done();
			}
		});
	});
	test('PUT with data', function (done) {
		var expected = '{"bar":"data"}';
		AnimeClient.ajax('ajax.php?data', {
			data: {bar:'data'},
			type: 'POST',
			success: function (res) {
				expect(res).to.be.equal(expected);
				done();
			},
			error: function (err) {
				expect.fail;
				done();
			}
		});
	});
	test('Bad request', function (done) {
		AnimeClient.ajax('ajax.php?bad', {
			error: function (status) {
				expect(status).to.be.equal(401);
				done();
			}
		});
	});
});