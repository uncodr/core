		<p>Configuration file <span class="code"><?= appFolder(); ?>config/database.php</span> already exists. It means that the <?= APP_NAME; ?> is already connected to a database.</p>
		<ul>
			<li>If you wish to use the existing database settings, then you can move to the next step of <strong>configuring the website</strong>.</li>
			<li>If you clear the database settings, then the current configuration saved in <span class="code bold"><?= appFolder(); ?>config/database.php</span> will be deleted. <strong>Note that this only deletes the settings, not the actual database</strong>, which means that you may use the existing database.</li>
		</ul>
		<p class="no-margin">
			<a href="setup/config" class="btn">Configure website</a> or <a href="setup/clean">clear database settings</a>
		</p>
