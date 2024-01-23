<?php if ($_->isAuthenticated()): ?>
	<main>
		<h2>
			Edit Manga List Item
		</h2>
		<form action="<?= $action ?>" method="post">
			<table class="invisible form">
				<thead>
				<tr>
					<th>
						<h3><?= $_->escape->html($item['manga']['title']) ?></h3>
						<?php foreach ($item['manga']['titles'] as $title): ?>
							<h4><?= $_->escape->html($title) ?></h4>
						<?php endforeach ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td rowspan="9">
						<?= $_->h->img($item['manga']['image']) ?>
					</td>
				</tr>
				<tr>
					<td><label for="status">Reading Status</label></td>
					<td>
						<select name="status" id="status">
							<?php foreach ($status_list as $val => $status): ?>
								<option <?php if ($item['reading_status'] === $val): ?>selected="selected"<?php endif ?>
										value="<?= $val ?>"><?= $status ?></option>
							<?php endforeach ?>
						</select>
					</td>
				</tr>
				<tr>
					<td><label for="series_rating">Rating</label></td>
					<td>
						<input type="number" min="0" max="10" maxlength="2" name="new_rating"
							   value="<?= $item['user_rating'] ?>" id="series_rating" size="2"/> / 10
					</td>
				</tr>
				<tr>
					<td><label for="chapters_read">Chapters Read</label></td>
					<td>
						<input type="number" min="0" name="chapters_read" id="chapters_read"
							   value="<?= $item['chapters']['read'] ?>"/> / <?= $item['chapters']['total'] ?>
					</td>
				</tr>
				<tr>
					<td><label for="rereading_flag">Rereading?</label></td>
					<td>
						<input type="checkbox" name="rereading" id="rereading_flag"
							   <?php if ($item['rereading'] === TRUE): ?>checked="checked"<?php endif ?>
						/>
					</td>
				</tr>
				<tr>
					<td><label for="reread_count">Reread Count</label></td>
					<td>
						<input type="number" min="0" id="reread_count" name="reread_count"
							   value="<?= $item['reread'] ?>"/>
					</td>
				</tr>
				<tr>
					<td><label for="notes">Notes</label></td>
					<td>
						<textarea name="notes" id="notes"><?= $_->escape->html($item['notes']) ?></textarea>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<input type="hidden" value="<?= $item['id'] ?>" name="id"/>
						<input type="hidden" value="<?= $item['mal_id'] ?>" name="mal_id"/>
						<input type="hidden" value="<?= $item['manga']['slug'] ?>" name="manga_id"/>
						<input type="hidden" value="<?= $item['user_rating'] ?>" name="old_rating"/>
						<input type="hidden" value="true" name="edit"/>
						<button type="submit">Submit</button>
					</td>
				</tr>
				</tbody>
			</table>
		</form>
		<fieldset>
			<legend>Danger Zone</legend>
			<form class="js-delete" action="<?= $_->urlFromRoute('manga.delete') ?>" method="post">
				<table class="form invisible">
					<tbody>
					<tr>
						<td class="danger">
							<strong>Permanently</strong> remove this list item and <strong>all</strong> its data?
						</td>
						<td>
							<input type="hidden" value="<?= $item['id'] ?>" name="id"/>
							<input type="hidden" value="<?= $item['mal_id'] ?>" name="mal_id"/>
							<button type="submit" class="danger">Delete Entry</button>
						</td>
					</tr>
					</tbody>
				</table>
			</form>
		</fieldset>
	</main>
<?php endif ?>