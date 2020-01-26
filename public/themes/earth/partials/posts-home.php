<?php if(isset($posts)) {
    foreach($posts as $post) { ?>
<section class="<?= (isset($post['meta']['class']))? $post['meta']['class'] : ''; ?> article">
	<header<?php if(isset($post['meta']['thumbnail'])) { ?> style="background-image: url('<?= $this->baseURL.$post['meta']['thumbnail']; ?>');"<?php } ?>>
		<h2 class="h1 title wrapper"><?= $post['title']; ?></h2>
	</header>
	<div class="body"><div class="wrapper">
<?= $post['content']."\n"; ?>
	</div></div>
</section>
<?php } } ?>
