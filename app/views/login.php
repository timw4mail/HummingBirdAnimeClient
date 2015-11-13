<main>
	<?= $message ?>
	<aside>
		<form method="post" action="<?= $urlGenerator->full_url($urlGenerator->path(), $url_type) ?>">
		<dl>
			<?php /*<dt><label for="username">Username: </label></dt>
			<dd><input type="text" id="username" name="username" required="required" /></dd>*/ ?>

			<dt><label for="password">Password: </label></dt>
			<dd><input type="password" id="password" name="password" required="required" /></dd>

			<dt>&nbsp;</dt>
			<dd><button type="submit">Login</button></dd>
		</dl>
		</form>
	</aside>
</main>