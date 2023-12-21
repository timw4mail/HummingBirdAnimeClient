<?php if ( ! $hasRequiredAnilistConfig): ?>
	<p class="static-message info">See the <a href="https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient/wiki/anilist">wiki</a> to learn how to set up Anilist integration. </p>
<?php else: ?>
	<?php $auth = $anilistModel->checkAuth(); ?>
	<?php if (array_key_exists('errors', $auth)): ?>
		<p class="static-message error">Anilist API Client is Not Authorized.</p>
		<?= $_->h->a(
			$_->urlFromRoute('anilist-redirect'),
			'Link Anilist Account',
			['class' => 'bracketed user-btn']
		) ?>
	<?php else: ?>
		<?php $expires = $_->config->get(['anilist', 'access_token_expires']); ?>
		<p class="static-message info">
			Linked to Anilist. Your access token will expire around <?= date('F j, Y, g:i a T', $expires) ?>
		</p>
		<?php require __DIR__ . '/_form.php' ?>
		<?= $_->h->a(
			$_->urlFromRoute('anilist-redirect'),
			'Update Access Token',
			['class' => 'bracketed user-btn']
		) ?>
	<?php endif ?>
<?php endif ?>