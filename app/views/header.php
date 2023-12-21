<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title><?= $title ?></title>
	<meta http-equiv="cache-control" content="no-store" />
	<meta http-equiv="Content-Security-Policy" content="script-src 'self'" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1" />
	<link rel="stylesheet" href="<?= $_->assetUrl('css/' . $_->config->get('theme') . '.min.css') ?>" />
	<link rel="<?= $_->config->get('theme') === 'dark' ? '' : 'alternate ' ?>stylesheet" title="Dark Theme" href="<?= $_->assetUrl('css/dark.min.css') ?>" />
	<link rel="icon" href="<?= $_->assetUrl('images/icons/favicon.ico') ?>" />
	<link rel="apple-touch-icon" sizes="57x57" href="<?= $_->assetUrl('images/icons/apple-icon-57x57.png') ?>">
	<link rel="apple-touch-icon" sizes="60x60" href="<?= $_->assetUrl('images/icons/apple-icon-60x60.png') ?>">
	<link rel="apple-touch-icon" sizes="72x72" href="<?= $_->assetUrl('images/icons/apple-icon-72x72.png') ?>">
	<link rel="apple-touch-icon" sizes="76x76" href="<?= $_->assetUrl('images/icons/apple-icon-76x76.png') ?>">
	<link rel="apple-touch-icon" sizes="114x114" href="<?= $_->assetUrl('images/icons/apple-icon-114x114.png') ?>">
	<link rel="apple-touch-icon" sizes="120x120" href="<?= $_->assetUrl('images/icons/apple-icon-120x120.png') ?>">
	<link rel="apple-touch-icon" sizes="144x144" href="<?= $_->assetUrl('images/icons/apple-icon-144x144.png') ?>">
	<link rel="apple-touch-icon" sizes="152x152" href="<?= $_->assetUrl('images/icons/apple-icon-152x152.png') ?>">
	<link rel="apple-touch-icon" sizes="180x180" href="<?= $_->assetUrl('images/icons/apple-icon-180x180.png') ?>">
	<link rel="icon" type="image/png" sizes="192x192"  href="<?= $_->assetUrl('images/icons/android-icon-192x192.png') ?>">
	<link rel="icon" type="image/png" sizes="32x32" href="<?= $_->assetUrl('images/icons/favicon-32x32.png') ?>">
	<link rel="icon" type="image/png" sizes="96x96" href="<?= $_->assetUrl('images/icons/favicon-96x96.png') ?>">
	<link rel="icon" type="image/png" sizes="16x16" href="<?= $_->assetUrl('images/icons/favicon-16x16.png') ?>">

</head>
<body class="<?= $_->escape->attr($url_type) ?> list">
	<?php include 'setup-check.php' ?>
	<header>
	<?php
		include 'main-menu.php';
		if(isset($message) && is_array($message))
		{
			foreach($message as $m)
			{
				$message = $m['message'];
				$message_type = $m['message_type'];
				include 'message.php';
			}
		}
	?>
	</header>