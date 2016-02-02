<?php if ($auth->is_authenticated()): ?>
<main>
	<h2>Add Manga to your List</h2>
	<form action="<?= $action_url ?>" method="post">
		<section>
			<div class="cssload-loader" hidden="hidden">
				<div class="cssload-inner cssload-one"></div>
				<div class="cssload-inner cssload-two"></div>
				<div class="cssload-inner cssload-three"></div>
			</div>
			<label for="search">Search for manga by name:&nbsp;&nbsp;&nbsp;&nbsp;<input type="search" id="search" /></label>
			<section id="series_list" class="media-wrap">
			</section>
		</section>
		<br />
		<table class="form">
			<tbody>
				<tr>
					<td><label for="status">Reading Status</label></td>
					<td>
						<select name="status" id="status">
						<?php foreach($status_list as $status_key => $status_title): ?>
							<option value="<?= $status_key ?>"><?= $status_title ?></option>
						<?php endforeach ?>
						</select>
					</td>
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
<script src="<?= $urlGenerator->asset_url('js.php?g=manga_collection') ?>"></script>
<?php endif ?>