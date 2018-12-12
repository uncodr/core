<?php
if(isset($page)) {
	if(file_exists(VIEWPATH.$page.'.php')) { $this->load->view($page); }
	else { echo "\t".'<p class="pad">View file <b>'.$page.'.php</b> does not exist.</p>'."\n"; }
}
elseif(isset($html)) {
	switch(gettype($html)) {
		case 'array':
			echo "\t".'<pre>'; print_r($html); echo '</pre>'."\n";
			break;
		case 'string':
		default:
			echo "\t".$html."\n";
			break;
	}
} ?>
