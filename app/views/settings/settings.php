<?php
if ( ! $auth->isAuthenticated())
{
	echo '<h1>Not Authorized</h1>';
	return;
}

$sectionMapping = [
	'anilist' => 'Anilist API Integration',
	'config' => 'General Settings',
	'cache' => 'Caching',
	'database' => 'Collection Database Settings',
];

$hiddenFields = [];
$nestedPrefix = 'config';
?>

<form action="<?= $url->generate('settings-post') ?>" method="POST">
	<main class='settings form'>
		<button type="submit">Save Changes</button>
		<div class="tabs">
			<?php $i = 0; ?>

			<?php foreach ($form as $section => $fields): ?>
				<input <?= $i === 0 ? 'checked="checked"' : '' ?> type="radio" id="settings-tab<?= $i ?>"
					name="settings-tabs"
				/>
				<label for="settings-tab<?= $i ?>"><h3><?= $sectionMapping[$section] ?></h3></label>
				<section class="content">
					<?php require __DIR__ . '/_form.php' ?>
					<?php if ($section === 'anilist'): ?>
						<hr />
						<?php $auth = $anilistModel->checkAuth(); ?>
						<?php if (array_key_exists('errors', $auth)): ?>
							<p class="static-message error">Not Authorized.</p>
							<?= $helper->a(
								$url->generate('anilist-redirect'),
								'Link Anilist Account'
							) ?>
						<?php else: ?>
							<?php $expires = $config->get(['anilist', 'access_token_expires']); ?>
							<p class="static-message info">
								Linked to Anilist. Your access token will expire around <?= date('F j, Y, g:i a T', $expires) ?>
							</p>
							<?= $helper->a(
								$url->generate('anilist-redirect'),
								'Update Access Token'
							) ?>
						<?php endif ?>
					<?php endif ?>
				</section>
				<?php $i++; ?>
			<?php endforeach ?>
		</div>
		<br />
		<?php foreach ($hiddenFields as $field): ?>
			<?= $field->__toString() ?>
		<?php endforeach ?>
		<button type="submit">Save Changes</button>
	</main>
</form>




