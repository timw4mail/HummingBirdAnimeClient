<?php if ($auth->isAuthenticated()): ?>
<main>
	<h2>Add <?= ucfirst($collection_type) ?> to your Collection</h2>
	<form action="<?= $action_url ?>" method="post">
		<section>
			<div class="cssload-loader" hidden="hidden">
				<div class="cssload-inner cssload-one"></div>
				<div class="cssload-inner cssload-two"></div>
				<div class="cssload-inner cssload-three"></div>
			</div>
			<label for="search">Search for <?= $collection_type ?> by name:&nbsp;&nbsp;&nbsp;&nbsp;<input type="search" id="search" name="search" /></label>
			<section id="series-list" class="media-wrap">
			</section>
		</section>
		<br />
		<table class="invisible form">
			<tbody>
				<tr>
					<td><label for="media_id">Media</label></td>
					<td>
						<select name="media_id" id="media_id">
						<?php foreach($media_items as $id => $name): ?>
							<option value="<?= $id ?>"><?= $name ?></option>
						<?php endforeach ?>
						</select>
					</td>
				</tr>
				<tr>
					<td><label for="notes">Notes</label></td>
					<td><textarea id="notes" name="notes"></textarea></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<button type="submit">Save</button>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</main>
<?php endif ?>