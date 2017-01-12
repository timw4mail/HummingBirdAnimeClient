<?php if ($auth->is_authenticated()): ?>
	<main>
		<h2>Edit Anime List Item</h2>
		<form action="<?= $action ?>" method="post">
			<table class="form">
				<thead>
					<tr>
						<th>
							<h3><?= $escape->html(array_shift($item['anime']['titles'])) ?></h3>
							<?php foreach($item['anime']['titles'] as $title): ?>
							<h4><?= $escape->html($title) ?></h4>
							<?php endforeach ?>
						</th>
						<th>
							<article class="media">
								<?= $helper->img($item['anime']['image']); ?>
							</article>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><label for="private">Is Private?</label></td>
						<td>
							<input type="checkbox" name="private" id="private"
							<?php if($item['private']): ?>checked="checked"<?php endif ?>
							/>
						</td>
					</tr>
					<tr>
						<td><label for="watching_status">Watching Status</label></td>
						<td>
							<select name="watching_status" id="watching_status">
							<?php foreach($statuses as $status_key => $status_title): ?>
								<option <?php if($item['watching_status'] === $status_key): ?>selected="selected"<?php endif ?>
									value="<?= $status_key ?>"><?= $status_title ?></option>
							<?php endforeach ?>
							</select>
						</td>
					</tr>
					<tr>
						<td><label for="series_rating">Rating</label></td>
						<td>
							<input type="number" min="0" max="10" maxlength="2" name="user_rating" id="series_rating" value="<?= $item['user_rating'] ?>" id="series_rating" size="2" /> / 10
						</td>
					</tr>
					<tr>
						<td><label for="episodes_watched">Episodes Watched</label></td>
						<td>
							<input type="number" min="0" size="4" maxlength="4" value="<?= $item['episodes']['watched'] ?>" name="episodes_watched" id="episodes_watched" />
								<?php if($item['episodes']['total'] > 0): ?>
								/ <?= $item['episodes']['total'] ?>
								<?php endif ?>
						</td>
					</tr>
					<tr>
						<td><label for="rewatching_flag">Rewatching?</label></td>
						<td>
							<input type="checkbox" name="rewatching" id="rewatching_flag"
								<?php if($item['rewatching'] === TRUE): ?>checked="checked"<?php endif ?>
							/>
						</td>
					</tr>
					<tr>
						<td><label for="rewatched">Rewatch Count</label></td>
						<td>
							<input type="number" min="0" id="rewatched" name="rewatched" value="<?= $item['rewatched'] ?>" />
						</td>
					</tr>
					<tr>
						<td><label for="notes">Notes</label></td>
						<td>
							<textarea name="notes" id="notes"><?= $escape->html($item['notes']) ?></textarea>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>
							<input type="hidden" value="<?= $item['id'] ?>" name="id" />
							<input type="hidden" value="<?= $item['mal_id'] ?>" name="mal_id" />
							<input type="hidden" value="true" name="edit" />
							<button type="submit">Submit</button>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		<fieldset>
			<legend>Danger Zone</legend>
			<form class="js-delete" action="<?= $url->generate('anime.delete') ?>" method="post">
				<table class="form invisible">
					<tbody>
						<tr>
							<td>&nbsp;</td>
							<td>
								<input type="hidden" value="<?= $item['id'] ?>" name="id" />
								<button type="submit" class="danger">Delete Entry</button>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</fieldset>
	</main>
	<script src="<?= $urlGenerator->asset_url('js.php/g/edit') ?>"></script>
<?php endif ?>