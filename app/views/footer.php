<section id="loading-shadow" hidden="hidden">
	<div class="loading-wrapper">
		<div class="loading-content">
			<h3>Updating List Item...</h3>
			<div class="cssload-loader">
				<div class="cssload-inner cssload-one"></div>
				<div class="cssload-inner cssload-two"></div>
				<div class="cssload-inner cssload-three"></div>
			</div>
		</div>
	</div>
</section>
<?php if ($auth->isAuthenticated()): ?>
<script nomodule async="async" defer="defer" src="<?= $urlGenerator->assetUrl('js/scripts-authed.min.js') ?>"></script>
<script type="module" src="<?= $urlGenerator->assetUrl('js/src/index-authed.js') ?>"></script>
<?php else: ?>
<script nomodule async="async" defer="defer" src="<?= $urlGenerator->assetUrl('js/scripts.min.js') ?>"></script>
<script type="module" src="<?= $urlGenerator->assetUrl('js/src/index.js') ?>"></script>
<?php endif ?>
</body>
</html>