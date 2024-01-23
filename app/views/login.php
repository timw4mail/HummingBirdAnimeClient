<main>
	<h2><?= $_->config->get('whose_list'); ?>'s Login</h2>
	<?= $message ?>
	<form method="post" action="<?= $_->urlFromRoute('login.post') ?>">
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