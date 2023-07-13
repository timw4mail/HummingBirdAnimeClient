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
					<?php
						($section === 'anilist')
							? require __DIR__ . '/_anilist.php'
							: require __DIR__ . '/_form.php'
					?>
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
