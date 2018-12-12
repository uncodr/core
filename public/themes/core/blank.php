<!DOCTYPE html>
<html lang="en">
<head>
<?php loadTemplate('partials/head', 'core'); ?>
    <link rel="stylesheet" type="text/css" href="<?= assetURL('css/fonts.css', 'core'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetURL('css/plugins.css', 'core'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetURL('css/style.css', 'core'); ?>">
</head>
<body class="<?= $bodyClass; ?>">

<?php
loadTemplate('partials/content', 'core');
if(isset($js)) {
	loadTemplate('partials/js-common', 'core');
	loadTemplate('partials/js-dynamic', 'core');
} ?>

</body>
</html>
