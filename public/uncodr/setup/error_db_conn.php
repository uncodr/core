		<p>We were unable to connect to the database using the settings provided. This could mean either of the following:</p>
		<ul>
			<li>Username <em class="crafty">&amp;</em> Password (<strong><?= $post['db_user'].' / '.$post['db_password']; ?></strong>) information is incorrect?</li>
			<li>Database server (<strong><?= $post['db_host']; ?></strong>) is inaccessible?</li>
		</ul>
		<p>Check the values once again, ensure that the DB hostname is correct and server is running. <strong>You may complete the first step manually also, by saving these details in a file</strong>. You need to go to the folder <span class="code"><?= appFolder(); ?>config</span>,</p>
		<ol>
			<li>open <span class="code">database.sample.php</span> with a text editor,</li>
			<li>fill these values, and</li>
			<li>save it as <span class="code">database.php</span> in the same folder.</li>
		</ol>
		<p>Once you have the right settings, you may&hellip;</p>
		<p><a href="setup/db" class="btn">Restart the installation</a></p>
		<footer>
			<p class="no-margin text-right">Not sure what this means? You should probably <strong>contact your server host</strong>.</p>
		</footer>
