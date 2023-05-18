<main class="details fixed">
	<section class="flex flex-no-wrap">
		<div>
			<?= $helper->img($data['image'], ['class' => 'cover' ]) ?>
		</div>
		<div>
			<h2 class="toph"><?= $data['name'] ?></h2>
			<?php foreach ($data['names'] as $name): ?>
				<h3><?= $name ?></h3>
			<?php endforeach ?>
			<?php if ( ! empty($data['birthday'])): ?>
				<h4><?= $data['birthday'] ?></h4>
			<?php endif ?>
			<br />
			<hr />
			<div class="description">
				<p><?= str_replace("\n", '</p><p>', $data['description']) ?></p>
			</div>
		</div>
	</section>

	<?php if ( ! empty($data['staff'])): ?>
		<section>
			<h3>Castings</h3>

			<div class="vertical-tabs">
				<?php $i = 0 ?>
				<?php foreach ($data['staff'] as $role => $entries): ?>
					<div class="tab">
						<input
							type="radio" name="staff-roles" id="staff-role<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> />
						<label for="staff-role<?= $i ?>"><?= $role ?></label>
						<?php foreach ($entries as $type => $casting): ?>
							<?php if (isset($entries['manga'], $entries['anime'])): ?>
								<h4><?= ucfirst($type) ?></h4>
							<?php endif ?>
							<section class="content media-wrap flex flex-wrap flex-justify-start">
								<?php foreach ($casting as $sid => $series): ?>
									<?php $mediaType = in_array($type, ['anime', 'manga'], TRUE) ? $type : 'anime'; ?>
									<?= $component->media(
											$series['titles'],
											$url->generate("{$mediaType}.details", ['id' => $series['slug']]),
											$helper->img($series['image'], ['width' => 220, 'loading' => 'lazy'])
									) ?>
								<?php endforeach; ?>
							</section>
						<?php endforeach ?>
					</div>
					<?php $i++ ?>
				<?php endforeach ?>
			</div>
		</section>
	<?php endif ?>

	<?php if ( ! empty($data['characters'])): ?>
		<section>
			<h3>Voice Acting Roles</h3>
			<?= $component->tabs('voice-acting-roles', $data['characters'], static function ($characterList) use ($component, $helper, $url) {
				$voiceRoles = [];
				foreach ($characterList as $cid => $item):
					$character = $component->character(
						$item['character']['canonicalName'],
						$url->generate('character', ['slug' => $item['character']['slug']]),
						$helper->img($item['character']['image'], ['loading' => 'lazy']),
					);
					$medias = [];
					foreach ($item['media'] as $sid => $series)
					{
						$medias[] = $component->media(
							$series['titles'],
							$url->generate('anime.details', ['id' => $series['slug']]),
							$helper->img($series['image'], ['width' => 220, 'loading' => 'lazy'])
						);
					}
					$media = implode('', array_map('mb_trim', $medias));

					$voiceRoles[] = <<<HTML
						<tr>
							<td>{$character}</td>
							<td>
								<section class="align-left media-wrap">{$media}</section>
							</td>
						</tr>
HTML;
				endforeach;

				$roles = implode('', array_map('mb_trim', $voiceRoles));

				return <<<HTML
					<table class="borderless max-table">
						<thead>
							<tr>
								<th>Character</th>
								<th>Series</th>
							</tr>
						</thead>
						<tbody>{$roles}</tbody>
					</table>
HTML;

			}) ?>
		</section>
	<?php endif ?>
</main>
