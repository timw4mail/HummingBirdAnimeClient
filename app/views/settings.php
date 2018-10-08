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
	<main class='form'>
		<button type="submit">Save Changes</button>
		<br />
		<?php foreach ($form as $section => $fields): ?>
		<fieldset class="box">
			<legend><?= $sectionMapping[$section] ?></legend>
			<section class='form'>
				<?php require __DIR__ . '/_form.php' ?>
			</section>
		</fieldset>
		<?php endforeach ?>

		<hr />
		<?php foreach ($hiddenFields as $field): ?>
			<?= $field ?>
		<?php endforeach ?>
		<button type="submit">Save Changes</button>
	</main>
</form>




