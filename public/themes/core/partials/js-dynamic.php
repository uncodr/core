<?php if(isset($js)) {
    $l = (isset($js['files']))? count($js['files']) : 0;
    for($i = 0; $i < $l; $i++) { ?>
<script type="text/javascript" src="<?= assetURL('js/'.$js['files'][$i].'.js', 'core'); ?>"></script>
<?php
    }
    $l = (isset($js['fn']))? count($js['fn']) : 0;
    for($i = 0; $i < $l; $i++) { ?>
<script type="text/javascript"><?= $js['fn'][$i]; ?>;</script>
<?php
    }
} ?>
