<main>
	<h2><a href="<?= $data['url'] ?>"><?= $data['title'] ?></a></h2>
	<?php if( ! empty($data['alternate_title'])): ?>
	<h3><?= $data['alternate_title'] ?></h3>
	<?php endif ?>
	
	<img src="<?= $data['cover_image'] ?>" alt="<?= $data['title'] ?> cover image" />

	<p><?= nl2br($data['synopsis']) ?></p>
	<pre><?= print_r($data, TRUE) ?></pre>
</main>