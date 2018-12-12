<!DOCTYPE html>
<html lang="en">
<head>
<?php loadTemplate('partials/head', 'core'); ?>
    <link rel="stylesheet" type="text/css" href="<?= assetURL('css/fonts.css', 'core'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetURL('css/plugins.css', 'core'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetURL('css/style.css', 'core'); ?>">
</head>
<body class="<?= $bodyClass; ?> minimal">

<div class="wrapper">
	<h1 class="app-title"><a href="<?= $this->baseURL; ?>" class="logo"><?= APP_NAME; ?></a></h1>
	<div class="panel">
		<header><h2 class="title"><?= $heading; ?></h2></header>
		<div class="body">
<?php loadTemplate('partials/content', 'core'); ?>
		</div>
	</div>
</div>

<?php
if(isset($js)) {
    loadTemplate('partials/js-common', 'core');
	loadTemplate('partials/js-dynamic', 'core');
} ?>

</body>
</html>
