<?php
use function Aviat\AnimeClient\getLocalImg;
use Aviat\AnimeClient\API\Kitsu;
?>
<main class="details fixed">
	<section class="flex flex-no-wrap">
		<div>
			<picture class="cover">
				<source
					srcset="<?= $urlGenerator->assetUrl("images/people/{$data['id']}-original.webp") ?>"
					type="image/webp"
				>
				<source
					srcset="<?= $urlGenerator->assetUrl("images/people/{$data['id']}-original.jpg") ?>"
					type="image/jpeg"
				>
				<img src="<?= $urlGenerator->assetUrl("images/people/{$data['id']}-original.jpg") ?>" alt="" />
			</picture>
		</div>
		<div>
			<h2><?= $data['attributes']['name'] ?></h2>
		</div>
	</section>

	<section>
		<?php if ($castCount > 0): ?>
			<h3>Castings</h3>
			<?php foreach ($castings as $role => $entries): ?>
				<h4><?= $role ?></h4>
				<?php foreach ($entries as $type => $casting): ?>
					<?php if ( ! empty($entries['manga'])): ?>
					<h5><?= ucfirst($type) ?></h5>
					<?php endif ?>
					<section class="align_left media-wrap">
					<?php foreach ($casting as $sid => $series): ?>
						<article class="media">
							<?php
							$link = $url->generate('anime.details', ['id' => $series['attributes']['slug']]);
							$titles = Kitsu::filterTitles($series['attributes']);
							?>
							<a href="<?= $link ?>">
								<picture>
									<source
										srcset="<?= $urlGenerator->assetUrl("images/{$type}/{$sid}.webp") ?>"
										type="image/webp"
									/>
									<source
										srcset="<?= $urlGenerator->assetUrl("images/{$type}/{$sid}.jpg") ?>"
										type="image/jpeg"
									/>
									<img
										src="<?= $urlGenerator->assetUrl("images/{$type}/{$sid}.jpg") ?>"
										width="220" alt=""
									/>
								</picture>
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
					<br />
				<?php endforeach ?>
			<?php endforeach ?>
		<?php endif ?>
	</section>
</main>
