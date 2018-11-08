<?php
use function Aviat\AnimeClient\getLocalImg;
use Aviat\AnimeClient\API\Kitsu;
?>
<h3>Voice Acting Roles</h3>
<div class="tabs">
<?php $i = 0; ?>
<?php foreach($data['characters'] as $role => $characterList): ?>
	<input <?= $i === 0 ? 'checked="checked"' : '' ?> type="radio" name="character-type-tabs" id="character-type-<?= $i ?>" />
	<label for="character-type-<?= $i ?>"><h5><?= ucfirst($role) ?></h5></label>
	<section class="content">
		<table class="borderless max-table">
			<tr>
				<th>Character</th>
				<th>Series</th>
			</tr>
			<?php foreach ($characterList as $cid => $character): ?>
				<tr>
					<td style="width:229px">
						<article class="character">
							<?php
							$link = $url->generate('character', ['slug' => $character['character']['slug']]);
							?>
							<a href="<?= $link ?>">
								<?php $imgPath = ($character['character']['image'] === NULL)
									? 'images/characters/empty.png'
									: getLocalImg($character['character']['image']['original']);

									echo $helper->picture($imgPath);
								?>
								<div class="name">
									<?= $character['character']['canonicalName'] ?>
								</div>
							</a>
						</article>
					</td>
					<td>
						<section class="align-left media-wrap">
							<?php foreach ($character['media'] as $sid => $series): ?>
								<article class="media">
									<?php
									$link = $url->generate('anime.details', ['id' => $series['slug']]);
									$titles = Kitsu::filterTitles($series);
									?>
									<a href="<?= $link ?>">
										<?= $helper->picture("images/anime/{$sid}.webp") ?>
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
	</section>
	<?php $i++ ?>
<?php endforeach ?>
</div>
