<?php
$setupErrors = \Aviat\AnimeClient\checkFolderPermissions($container->get('config'));
?>

<?php if ( ! empty($setupErrors)): ?>
<aside class="message error">
	<h1>Issues with server setup:</h1>

	<?php if (array_key_exists('missing', $setupErrors)): ?>
		<h3>The following folders need to be created, and writable.</h3>
		<ul>
			<?php foreach ($setupErrors['missing'] as $error): ?>
				<li><?= $error ?></li>
			<?php endforeach ?>
		</ul>
	<?php endif ?>

	<?php if (array_key_exists('writable', $setupErrors)): ?>
	<h3>The following folders are not writable by the server.</h3>
	<ul>
		<?php foreach($setupErrors['writable'] as $error): ?>
		<li><?= $error ?></li>
		<?php endforeach ?>
	</ul>
	<?php endif ?>
</aside>
<?php endif ?>