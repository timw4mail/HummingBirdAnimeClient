<?php
use function Aviat\AnimeClient\getLocalImg;
use Aviat\AnimeClient\API\Kitsu;
?>
<?php foreach ($entries as $type => $casting): ?>
	<?php if($type === 'characters'): ?>
	<table class="min-table">
		<tr>
			<th>Character</th>
			<th>Series</th>
		</tr>
		<?php foreach ($casting as $cid => $character): ?>
			<tr>
				<td style="width:229px">
					<article class="character">
						<?php
						$link = $url->generate('character', ['slug' => $character['character']['slug']]);
						?>
						<a href="<?= $link ?>">
							<?php $imgPath = ($character['character']['image'] === NULL)
								? $urlGenerator->assetUrl('images/characters/empty.png')
								: $urlGenerator->assetUrl(getLocalImg($character['character']['image']['original']));
							?>
							<img src="<?= $imgPath ?>" alt="" />
							<div class="name">
								<?= $character['character']['canonicalName'] ?>
							</div>
						</a>
					</article>
				</td>
				<td>
					<section class="align_left media-wrap">
						<?php foreach ($character['media'] as $sid => $series): ?>
							<article class="media">
								<?php
								$link = $url->generate('anime.details', ['id' => $series['slug']]);
								$titles = Kitsu::filterTitles($series);
								?>
								<a href="<?= $link ?>">
									<img
										src="<?= $urlGenerator->assetUrl("images/anime/{$sid}.jpg") ?>"
										width="220" alt=""
									/>
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
						<?php endforeach ?>
					</section>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php endif ?>
<?php endforeach ?>
