<?php if ($auth->is_authenticated()): ?>
<main>
	<h2>Add Anime to your List</h2>
	<form action="<?= $action_url ?>" method="post">
		<section>
			<label for="search">Search for anime by name:&nbsp;&nbsp;&nbsp;&nbsp;<input type="search" id="search" /></label>
			<section id="series_list" class="media-wrap">
			</section>
		</section>
		<br />
		<table class="form">
			<tbody>
				<tr>
					<td><label for="status">Watching Status</label></td>
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
<template id="show_list">
	<article class="media">
		<div class="name"><label><input type="radio" name="id" value="{{:slug}}" />&nbsp;<span>{{:title}}<br />{{:alternate_title}}</span></label></div>
		<img src="{{:cover_image}}" alt="{{:title}}" />
	</article>
</template>
<script src="<?= $urlGenerator->asset_url('js.php?g=anime_collection') ?>"></script>
<?php endif ?>