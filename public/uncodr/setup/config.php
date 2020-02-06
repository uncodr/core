		<?= form_open(($this->baseURL).'setup/finish', ['class' => 'left-labelled']); ?>
			<label>
				<span class="field-name">Website Title</span>
				<input type="text" name="title" required>
			</label>
			<p class="hint">Example: John's Portfolio</p>
			<label>
				<span class="field-name">Username</span>
				<input type="text" name="user" value="" required>
			</label>
			<p class="hint">Admin to manage <?= APP_NAME; ?></p>
			<label>
				<span class="field-name">Password</span>
				<input type="password" name="password" value="" required>
			</label>
			<p class="hint">Admin Password</p>
			<label>
				<span class="field-name">Email</span>
				<input type="email" name="email" value="" required>
			</label>
			<p class="hint">Admin's email ID</p>
			<button type="submit" class="btn btn-submit">Finish Setup</button>
		</form>
