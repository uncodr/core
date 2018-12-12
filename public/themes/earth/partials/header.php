<header class="topbar"><div class="wrapper-fluid">
	<h1 class="app-title"><a href="<?= $this->baseURL; ?>"><span class="logo mini hidden-home"></span> <?= $this->siteConfigs['siteTitle']; ?></a></h1>
	<ul class="nav-primary list-group pull-right bold">
<?php foreach($navMenu as $href => $link) { ?>
        <li<?= ($link['class'])? ' class="'.$link['class'].'"':''; ?>><a href="<?= $href; ?>"><?= $link['text']; ?></a></li>
<?php } ?>
	</ul>
</div></header>
