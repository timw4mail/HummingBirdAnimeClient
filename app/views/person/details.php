<?php

use Aviat\AnimeClient\API\Kitsu;

?>
<main class="details fixed">
	<section class="flex flex-no-wrap">
		<div>
			<?= $helper->picture("images/people/{$data['id']}-original.webp", 'jpg', ['class' => 'cover' ]) ?>
		</div>
		<div>
			<h2 class="toph"><?= $data['attributes']['name'] ?></h2>
		</div>
	</section>

	<?php if ( ! empty($staff)): ?>
		<section>
			<h3>Castings</h3>
			<div class="vertical-tabs">
				<?php $i = 0 ?>
				<?php foreach ($staff as $role => $entries): ?>
					<div class="tab">
						<input
							type="radio" name="staff-roles" id="staff-role<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> />
						<label for="staff-role<?= $i ?>"><?= $role ?></label>
						<?php foreach ($entries as $type => $casting): ?>
							<?php if ($type === 'characters') continue; ?>
							<?php if ( ! (empty($entries['manga']) || empty($entries['anime']))): ?>
								<h4><?= ucfirst($type) ?></h4>
							<?php endif ?>
							<section class="content">
								<?php foreach ($casting as $sid => $series): ?>
									<article class="media">
										<?php
										$mediaType = (in_array($type, ['anime', 'manga'])) ? $type : 'anime';
										$link = $url->generate("{$mediaType}.details", ['id' => $series['slug']]);
										$titles = Kitsu::filterTitles($series);
										?>
										<a href="<?= $link ?>">
											<?= $helper->picture("images/{$type}/{$sid}.webp") ?>
										</a>
										<div class="name">
											<a href="<?= $link ?>">
												<?= array_shift($titles) ?>
												<?php foreach ($titles as $title): ?>
													<br />
													<small><?= $title ?></small>
												<?php endforeach ?>
											</a>
										</div>
									</article>
								<?php endforeach; ?>
							</section>
						<?php endforeach ?>
					</div>
					<?php $i++ ?>
				<?php endforeach ?>
			</div>
		</section>
	<?php endif ?>

	<?php if ( ! (empty($characters['main']) || empty($characters['supporting']))): ?>
		<section>
			<?php include 'character-mapping.php' ?>
		</section>
	<?php endif ?>
</main>
