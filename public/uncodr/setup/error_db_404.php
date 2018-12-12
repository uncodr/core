		<p>Username <em class="crafty">&amp;</em> password values are working fine. However, we were unable to select the database <strong>'<?= $post['db_name']; ?>'</strong>. This could mean either of the following:</p>
		<ul>
			<li>Database <strong><?= $post['db_name']; ?></strong> does not exist?</li>
			<li>User <strong><?= $post['db_user']; ?></strong> does not have permission to access this database?</li>
		</ul>
		<p>You will need to create a database <strong><?= $post['db_name']; ?></strong> and give user <strong><?= $post['db_user']; ?></strong> complete access to it.</p>
		<p>Once this is done, you may&hellip;</p>
		<p><a href="setup/db" class="btn">Restart the installation</a></p>
		<footer>
			<p class="no-margin text-right">Not sure what this means? You should probably <strong>contact your server host</strong>.</p>
		</footer>
