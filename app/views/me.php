<main class="details">
	<section class="flex flex-no-wrap">
		<div>
			<h2><?= $attributes['name'] ?></h2>
			<img src="<?= $attributes['avatar']['original'] ?>" alt="" />
			<br />
			<br />
			<table class="media_details">
				<tr>
					<td>Location</td>
					<td><?= $attributes['location'] ?></td>
				</tr>
			</table>
		</div>
		<div>
			<dl>
				<dt>About:</dt>
				<dd><?= $attributes['bio'] ?></dd>
			</dl>
			<pre><?= json_encode($attributes, \JSON_PRETTY_PRINT) ?></pre>
			<pre><?= json_encode($relationships, \JSON_PRETTY_PRINT) ?></pre>
			<pre><?= json_encode($included, \JSON_PRETTY_PRINT) ?></pre>
		</div>
	</section>
</main>