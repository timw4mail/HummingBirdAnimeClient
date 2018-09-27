<?php
use function Aviat\AnimeClient\loadTomlByFile;

$settings = loadTomlByFile($config->get('config_dir'));

if ( ! $auth->isAuthenticated())
{
	echo '<h1>Not Authorized</h1>';
	return;
}


function render_settings_form ($data, $file)
{
	ob_start();
	foreach ($data as $key => $value)
	{
		?>
<tr>
	<td><label for="<?= $key ?>"><?= $key ?></label></td>
	<td>
		<?php if (is_scalar($value)): ?>
		<input
			type="text"
			id="<?= $key ?>"
			name="config[<?= $file ?>][<?= $key ?>]"
			value="<?= $value ?>"
		/>
		<?php else: ?>
			<table><?= render_settings_form($value, $file); ?></table>
		<?php endif ?>
	</td>
</tr>
	<?php
	}

	$buffer = ob_get_contents();
	ob_end_clean();

	return $buffer;
}

?>

<pre><?= print_r($_POST, TRUE) ?></pre>

<?php foreach($settings as $file => $properties): ?>
<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="POST">
<table class="form">
	<caption><?= $file ?></caption>
	<tbody>
	<?= render_settings_form($properties, $file); ?>
	</tbody>
</table>
	<button type="submit">Save Changes</button>
</form>
<?php endforeach ?>




