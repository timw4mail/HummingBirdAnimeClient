<?php if ( ! $hasRequiredAnilistConfig): ?>
	<p class="static-message info">See the <a href="https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient/wiki/anilist">wiki</a> to learn how to set up Anilist integration. </p>
<?php else: ?>
	<?php $auth = $anilistModel->checkAuth(); ?>
	<?php if (array_key_exists('errors', $auth)): ?>
		<p class="static-message error">Anilist API Client is Not Authorized.</p>
		<?= $helper->a(
			$url->generate('anilist-redirect'),
			'Link Anilist Account',
			['class' => 'bracketed user-btn']
		) ?>
	<?php else: ?>
		<?php $expires = $config->get(['anilist', 'access_token_expires']); ?>
		<p class="static-message info">
			Linked to Anilist. Your access token will expire around <?= date('F j, Y, g:i a T', $expires) ?>
		</p>
		<?php require __DIR__ . '/_form.php' ?>
		<?= $helper->a(
			$url->generate('anilist-redirect'),
			'Update Access Token',
			['class' => 'bracketed user-btn']
		) ?>
	<?php endif ?>
<?php endif ?>