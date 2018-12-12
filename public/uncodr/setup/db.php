		<?= form_open(($this->baseURL).'setup/install', ['class' => 'left-labelled']); ?>
<?php /*			<div class="hidden">
				<label>
					<span class="field-name">DB Type</span>
					<span class="select">
						<select name="db_driver">
							<option value="mysqli" selected="selected">MySQL</option>
							<option value="mssql">SQL Server</option>
							<option value="cubrid">Cubrid</option>
							<option value="ibase">iBase</option>
							<option value="oci8">OCI8</option>
							<option value="odbc">ODBC</option>
							<option value="pdo">PDO</option>
							<option value="postgre">PostGRE</option>
							<option value="sqlite">SQLite</option>
							<option value="sqlite3">SQLite3</option>
							<option value="sqlsrv">SQLSRV</option>
						</select><span></span>
					</span>
				</label>
				<p class="hint">Choose MySQL if not sure about this</p>
			</div> */ ?>
			<label>
				<span class="field-name">Hostname</span>
				<input type="text" name="db_host" value="localhost" required>
			</label>
			<p class="hint">Database host: Get in touch with your server admin if 'localhost' does not work</p>
			<label>
				<span class="field-name">Username</span>
				<input type="text" name="db_user" value="" required>
			</label>
			<p class="hint">Database Username</p>
			<label>
				<span class="field-name">Password</span>
				<input type="text" name="db_password" value="" required>
			</label>
			<p class="hint">Database Password</p>
			<label>
				<span class="field-name">DB Name</span>
				<input type="text" name="db_name" value="<?= strtolower(APP_NAME); ?>" required>
			</label>
			<p class="hint">Name of the Database that the site will use</p>
			<label>
				<span class="field-name">Table Prefix</span>
				<input type="text" name="db_prefix" value="uc_" required>
			</label>
			<p class="hint">If you wish to have multiple installations of <?= APP_NAME; ?> in the same database</p>
			<label class="no-margin">
				<span class="field-name">Make it fresh</span>
				<span class="checkbox"><input type="checkbox" name="clean" value="1"><span></span></span>
			</label>
			<p class="hint">Select this to have a completely fresh <?= APP_NAME; ?> instance (<strong>delete all the existing data</strong>)</p>
			<input type="hidden" name="db_driver" value="mysqli">
			<button type="submit" class="btn btn-submit">Install Database</button>
		</form>
		<footer>
			<p class="no-margin text-right">Not sure what this means? You should probably <strong>contact your server host</strong>.</p>
		</footer>
