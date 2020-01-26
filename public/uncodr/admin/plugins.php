	<div class="page-heading">
		<input type="text" name="search" placeholder="Search" class="search">
		<h1><?= $heading; ?></h1>
		<a href="admin/plugins#add" class="btn sm">Add New</a>
	</div>
	<ul class="list-inline toolbar">
		<li class="btn-group bulk-axn hidden">
			<a class="btn btn-default" data-action="edit"><i class="ion ion-play"></i></a>
			<a class="btn btn-default" data-action="trash"><i class="ion ion-stop"></i></a>
			<a class="btn btn-default" data-action="trash"><i class="ion ion-loop"></i></a>
			<a class="btn btn-default" data-action="trash"><i class="ion ion-trash-b"></i></a>
		</li>
		<li class="btn-group">
			<a class="btn btn-default filter active" data-filter="published">Active (2)</a>
			<a class="btn btn-default filter" data-filter="drafts">Inactive (1)</a>
		</li>
	</ul>
	<table class="panel multi-row zebra condensed">
		<thead>
			<tr>
				<th class="width-min"><span class="checkbox"><input type="checkbox" data-group="check" data-axn="bulk-axn" class="multicheck all" value=""><span></span></span></th>
				<th class="width-34">Plugin</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			<tr class="template hidden has-hover-tools">
				<td><span class="checkbox"><input type="checkbox" data-group="check" class="multicheck" value="{{plugin}}"><span></span></span></td>
				<td>
					<h4><a href="admin/plugins/{{plugin}}">{{name}}</a></h4>
					<ul class="list-inline separate hover-tools text sm">
						<li><a href="admin/plugins/{{plugin}}">Settings</a></li>
						<li><a class="btn-status" data-status="1" data-plugin="{{plugin}}">Activate</a></li>
						<li><a class="btn-status" data-status="0" data-plugin="{{plugin}}">Deactivate</a></li>
						<li><a class="btn-delete danger" data-plugin="{{plugin}}">Delete</a></li>
					</ul>
				</td>
				<td>
					<p>{{description}}</p>
					<ul class="list-inline no-margin separate">
						<li>{{version}}</li>
						<li>by <a class="bold" href="{{siteAuthor}}" target="_blank">{{author}}</a></li>
						<li><a href="{{sitePlugin}}" target="_blank">Details</a></li>
					</ul>
				</td>
			</tr>
			<tr class="has-hover-tools">
				<td><span class="checkbox"><input type="checkbox" data-group="check" class="multicheck" value="jlms"><span></span></span></td>
				<td>
					<h4><a href="admin/plugins/jlms">Jamboree LMS</a></h4>
					<ul class="list-inline separate hover-tools text sm">
						<li><a href="admin/plugins/jlms">Settings</a></li>
						<li><a class="btn-status" data-status="0" data-plugin="jlms">Deactivate</a></li>
					</ul>
				</td>
				<td>
					<p>This is Jamboree's Learning Management System.</p>
					<ul class="list-inline no-margin separate">
						<li>v3.0</li>
						<li>by <a class="bold" href="https://jamboree.online" target="_blank">Jamboree</a></li>
						<li><a href="https://uncodr.com/plugins/jlms" target="_blank">Details</a></li>
					</ul>
				</td>
			</tr>
			<tr class="has-hover-tools">
				<td><span class="checkbox"><input type="checkbox" data-group="check" class="multicheck" value="forum"><span></span></span></td>
				<td>
					<h4><a href="admin/plugins/forum">Forum</a></h4>
					<ul class="list-inline separate hover-tools text sm">
						<li><a href="admin/plugins/forum">Settings</a></li>
						<li><a class="btn-status" data-status="0" data-plugin="forum">Deactivate</a></li>
					</ul>
				</td>
				<td>
					<p>Integrate a Q&amp;A Forum on your website through this plugin.</p>
					<ul class="list-inline no-margin separate">
						<li>v1.0</li>
						<li>by <a class="bold" href="https://jamboree.online" target="_blank">Jamboree</a></li>
						<li><a href="https://uncodr.com/plugins/forum" target="_blank">Details</a></li>
					</ul>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<th><span class="checkbox"><input type="checkbox" data-group="check" class="multicheck all" value=""><span></span></span></th>
				<th>Plugin</th>
				<th>Description</th>
			</tr>
		</tfoot>
	</table>
