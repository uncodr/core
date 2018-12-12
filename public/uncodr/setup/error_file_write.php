		<p>DB settings you provided are working fine. However, we were unable to save them in <span class="code"><?= appFolder(); ?>config/database.php</span> file.</p>
		<p class="bold">You may open <span class="code"><?= appFolder(); ?>config/database.php</span> with a text editor, and save the following text in it.</p>
		<textarea class="block"><?= $content; ?></textarea>
		<p>Once this is done, you may&hellip;</p>
		<p><a href="setup/db" class="btn">Continue</a></p>
