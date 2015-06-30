<main>
	<?= $message ?>
	<aside>
		<form method="post" action="<?= full_url('/login', $url_type) ?>">
		<dl>
			<?php /*<dt><label for="username">Username: </label></dt>
			<dd><input type="text" id="username" name="username" required="required" /></dd>*/ ?>

			<dt><label for="password">Password: </label></dt>
			<dd><input type="password" id="password" name="password" required="required" /></dd>

			<dt>&nbsp;</dt>
			<dd><input type="submit" value="Login" /></dd>
		</dl>
		</form>
	</aside>
</main>