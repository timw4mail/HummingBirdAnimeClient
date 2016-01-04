<main>
	<h2><?= $config->get('whose_list'); ?>'s Login</h2>
	<?= $message ?>
	<form method="post" action="<?= $urlGenerator->full_url($urlGenerator->path(), $url_type) ?>">
		<table class="form invisible">
			<tr>
				<td><label for="password">Password: </label></td>
				<td><input type="password" id="password" name="password" required="required" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit">Login</button></td>
			</tr>
		</table>
	</form>
</main>