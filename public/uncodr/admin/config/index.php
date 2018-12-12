<?php
# general settings:
# - tagline, wp URL, site URL, week starts on, site language
# writing settings:
# - default post category, default post format
# - post via email: mail server, login, password
# reading settings:
# - posts page, feed settings (full text/summary)
# discussion settings:
# - pingbacks and trackbacks
# - allow nested comments with depth?,
# - only approved comment authors can comment
# - comment moderation queuing and blacklisting using keywords/email/IP/name/URL/number of links in comment
# - enable avatar, rating, default avatar
# media settings:
# - thumbnail size, medium size, large size
# permalinks format:
# - plain, day+name (y/m/d/slug), custom
# --- allowed tags: year, month, day, hour, minute, seconds, id, postname, category, author
# - tag base and category base
?>
	<div class="page-heading">
		<h1><?= $heading; ?></h1>
		<a href="admin/config/advanced" class="btn sm">Advanced</a>
	</div>
	<ul class="nav-tabs bold border">
		<li><a class="tab-links" data-target=".tab-wrapper:.general" data-href="#general">General</a></li>
		<li><a class="tab-links" data-target=".tab-wrapper:.posts" data-href="#posts">Posts</a></li>
		<li><a class="tab-links" data-target=".tab-wrapper:.security" data-href="#security">Security</a></li>
	</ul>
	<form class="left-labelled config-form">
		<ul class="list-unstyled tab-wrapper general row hidden">
			<li class="col sm-6">
				<label>
					<span class="field-name">Site Title</span>
					<input type="text" name="site_title" value="" required>
				</label><br>
				<label>
					<span class="field-name">Admin Email</span>
					<input type="text" name="site_admin" value="" required>
				</label><br>
				<label>
					<span class="field-name">Site Visibility</span>
					<span class="right field-name"><span class="checkbox"><input type="checkbox" name="is_crawlable" value="0"><span></span></span>Remove from Search Engines</span>
				</label>
				<p class="hint">Few search engines may not honour this request</p>
				<span class="label">Registration</span>
				<ul class="list-inline spaced right">
					<li><label>
						<span class="radio"><input name="registration[enable]" type="radio" value="1" data-more="reg" required><span></span></span>Open
					</label></li>
					<li><label>
						<span class="radio"><input name="registration[enable]" type="radio" value="0" data-more="reg" required><span></span></span>Closed
					</label></li>
				</ul>
				<div class="right blocked more-reg pad0-7" data-val="1">
					<ul class="list-unstyled condensed">
						<li><label>
							<span class="checkbox"><input type="checkbox" name="registration[autologin]" value="1"><span></span></span>Automatically login on registration
						</label></li>
						<li><label>
							<span class="checkbox"><input name="notification[]" type="checkbox" value="registration"><span></span></span>Notify admin via email when someone registers
						</label></li>
					</ul>
					<p>Select new registrant's default group</p>
					<span class="select">
						<select name="registration[default_group]">
							<option value="" disabled="disabled">Select Group</option>
<?php foreach($groups as $code => $name) { ?>
							<option value="<?= $code; ?>"><?= $name; ?></option>
<?php } ?>
						</select><span></span>
					</span>
				</div>
			</li>
			<li class="col sm-6">
				<label>
					<span class="field-name">Timezone</span>
					<span class="select">
<?php
$this->load->helper('date');
echo timezone_menu('', null, 'timezone')."\n";
?>
					<span></span></span>
				</label>
				<p class="hint">Current Server time (<abbr>UTC</abbr>) is <?= date('F d, Y - H:i:s'); ?>.</p>
				<span class="label">Date Format</span>
				<ul class="list-unstyled condensed right">
<?php
# date formats
$options = ['F d, Y', 'Y-m-d', 'j/m/Y'];
foreach ($options as $val) { ?>
					<li><label><span class="radio"><input name="date_format" type="radio" value="<?= $val; ?>"><span></span></span><?= $val; ?></label></li>
<?php } ?>
					<li><label><span class="radio"><input name="date_format" type="radio" value=""><span></span></span><input type="text" class="mini inline-block custom" placeholder="Custom"></label></li>
				</ul>
				<span class="label">Time Format</span>
				<ul class="list-unstyled condensed right">
<?php
# time formats
$options = ['H:i:s', 'h:i:s a', 'h:i:s A'];
foreach ($options as $val) { ?>
					<li><label><span class="radio"><input name="time_format" type="radio" value="<?= $val; ?>"><span></span></span><?= $val; ?></label></li>
<?php } ?>
					<li>
						<span class="radio"><input name="time_format" type="radio" value=""><span></span></span><input type="text" class="mini inline-block custom" placeholder="Custom">
						<br><a href="<?= APP_URL; ?>help/settings#time" target="_blank">Help on Date <span class="crafty">&amp;</span> Time Format</a>
					</li>
				</ul>
				<div class="text sm lite">
					<span class="label">Example</span><span class="dt-sample" data-value="<?= time(); ?>"></span>
				</div>
			</li>
		</ul>
		<ul class="list-unstyled tab-wrapper posts row hidden">
			<li class="col sm-6">
				<span class="label">Homepage</span>
				<ul class="list-unstyled condensed right">
<?php
# homepage options
$options = [];
if(file_exists(FCPATH.APPDIR)) { $options['app'] = 'App'; }
$options['blog'] = 'Blog (Recent Articles)';
$options['page'] = 'Static Page';
foreach ($options as $key => $val) { ?>
					<li><label><span class="radio"><input name="homepage" type="radio" value="<?= $key; ?>"><span></span></span><?= $val; ?></label></li>
<?php } ?>
					<li class="select hidden indent-left more-homepage" data-val="page">
						<select name="page_id">
							<option value="" disabled="disabled">Page</option>
						</select><span></span>
					</li>
				</ul>
				<p class="hint">What to display on the front page of the website</p>
				<label>
					<span class="field-name">Blog pages show</span>
					<span class="right"><input type="number" class="mini" name="blog_count" value="" min="1" required> articles or less</span>
				</label>
				<span class="label">URL Format</span>
				<ul class="list-unstyled condensed right">
<?php
# permalinks format
$options = [':slug' => 'Title (Default)', ':y/:m/:slug' => 'Month and Title', 'posts/:id' => 'Post ID'];
foreach ($options as $key => $val) { ?>
					<li><label><span class="radio"><input name="perma_links" type="radio" value="<?= $key; ?>"><span></span></span><?= $val; ?></label></li>
<?php } ?>
					<li>
						<span class="radio"><input name="perma_links" type="radio" value=""><span></span></span><input type="text" class="inline-block custom" placeholder="Custom">
						<br><a href="<?= APP_URL; ?>help/settings#url_format" target="_blank">Help on URL Format</a>
					</li>
				</ul>
				<div class="text sm lite">
					<span class="label">Example</span> <?= $this->baseURL; ?><span class="perma-sample"></span>
				</div>
			</li>
			<li class="col sm-6">
				<span class="label">Comments are</span>
				<ul class="list-inline spaced right margin">
					<li><label><span class="radio"><input name="comments[enable]" type="radio" value="1" data-more="comments" required><span></span></span>Enabled</label></li>
					<li><label><span class="radio"><input name="comments[enable]" type="radio" value="0" data-more="comments" required><span></span></span>Disabled</label></li>
				</ul>
				<div class="more-comments blocked pad0-7" data-val="1">
					<span class="label">Show</span>
					<ul class="list-inline spaced right">
						<li><label><span class="radio"><input name="comments[sort]" type="radio" value="ASC"><span></span></span>Oldest</label></li>
						<li><label><span class="radio"><input name="comments[sort]" type="radio" value="DESC"><span></span></span>Newest</label></li>
						<li>comments first</li>
					</ul>
					<ul class="list-unstyled condensed right">
						<li>
							<label><span class="checkbox"><input type="checkbox" name="comments[author_info]" value="1"><span></span></span>Comment writer must fill name and email</label>
						</li>
						<li>
							<label><span class="checkbox"><input type="checkbox" name="comments[public]" value="0"><span></span></span>User must be logged in for commenting</label>
						</li>
						<li>
							<label><span class="checkbox"><input type="checkbox" name="comments[moderation]" value="1"><span></span></span>Comments are manually approved</label>
						</li>
						<li>
							Autoclose comments on articles older than <input type="number" class="mini" name="comments[autoclose]" value="0" min="0"> days.<br><span class="lite">(Use '0' to disable autoclosing of comments).</span>
						</li>
					</ul>
				</div><br>
				<p>Notify admin via email when <span class="checkbox"><input type="checkbox" class="multicheck all"><span></span></span></p>
				<ul class="list-unstyled spaced right">
					<li><label>
						<span class="checkbox"><input name="notification[]" type="checkbox" class="multicheck" value="post"><span></span></span>a post is published
					</label></li>
					<li><label>
						<span class="checkbox"><input name="notification[]" type="checkbox" class="multicheck" value="comment"><span></span></span>someone writes a comment
					</label></li>
					<li><label>
						<span class="checkbox"><input name="notification[]" type="checkbox" class="multicheck" value="comment_moderation"><span></span></span>a comment is held for moderation
					</label></li>
				</ul>
			</li>
		</ul>
		<ul class="list-unstyled tab-wrapper security row hidden">
			<li class="col sm-6">
				<span class="label">Login</span>
				<ul class="list-unstyled condensed right">
					<li>
						<label><span class="checkbox"><input type="checkbox" name="uvlimit" value="1"><span></span></span>Only verified users can login</label>
						<label class="indent-left more-uvlimit hidden" data-val="0">Unverified users can login for <input type="number" class="mini inline" name="login[unverified_limit]" value="" min="0"> days</label>
					</li>
					<li>
						<label><span class="checkbox"><input type="checkbox" name="login[disable_on_expiry]" value="1"><span></span></span>Disable login if user's group access expires</label>
					</li>
				</ul>
			</li>
			<li class="col sm-6">
				<label>
					<span class="field-name">Email Server</span>
					<span class="select">
						<select name="email[host]">
							<option value="" data-port="">None</option>
							<option value="smtp.gmail.com" data-port="587">GMail</option>
							<option value="smtp.sendgrid.net" data-port="587">SendGrid</option>
							<option value="smtp.mail.yahoo.com" data-port="587">Yahoo</option>
							<option value="smtp.live.com" data-port="587">Outlook</option>
							<option value="smtp.office365.com" data-port="587">Office365</option>
							<option value="smtp.aol.com" data-port="587">AOL</option>
						</select><span></span>
						<input type="hidden" name="email[port]" value="587">
					</span>
				</label>
				<p class="hint">SMTP Server you want to use for sending emails<br>Choose 'None' to disable emailing through smtp</p>
				<label>
					<span class="field-name">Username</span>
					<input type="text" name="email[user]" value="">
				</label>
				<p class="hint">Username of your smtp server's account</p>
				<label>
					<span class="field-name">Password</span>
					<input type="password" name="email[pass]" value="">
				</label>
				<p class="hint">Password of your smtp server's account</p>
				<p class="right"><a href="<?= APP_URL; ?>help/settings#email" target="_blank">Help on Email Settings</a></p>
			</li>
		</ul>
		<ul class="list-inline toolbar clearfix">
			<li>
				<button class="btn btn-save" type="submit">Save</button>
			</li>
			<li>
				<a class="btn btn-default btn-reset">Reset</a>
			</li>
		</ul>
	</form>
