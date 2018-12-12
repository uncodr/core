	<div class="page-heading">
		<h1><?= $heading; ?></h1>
		<span class="search"><input type="text" name="q"></span>
		<a href="admin/plugins#add" class="btn sm">Add New</a>
	</div>
	<ul class="list-inline toolbar">
		<li class="btn-group bulk-axn hidden">
			<a class="btn btn-default" data-action="edit"><i class="ion ion-play"></i></a>
			<a class="btn btn-default" data-action="trash"><i class="ion ion-stop"></i></a>
			<a class="btn btn-default" data-action="trash"><i class="ion ion-loop"></i></a>
			<a class="btn btn-default" data-action="trash"><i class="ion ion-trash-b"></i></a>
		</li>
		<li class="btn-group view">
			<a class="btn btn-default filter active" data-filter="published">Active (2)</a>
			<a class="btn btn-default filter" data-filter="drafts">Inactive (1)</a>
		</li>
	</ul>
	<table class="panel multi-row zebra condensed">
		<thead>
			<tr>
				<th class="width-min"><span class="checkbox"><input type="checkbox" data-group="check1" data-axn="bulk-axn" class="multicheck all" value=""><span></span></span></th>
				<th class="width-34">Plugin</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			<tr class="has-hover-tools">
				<td><span class="checkbox"><input type="checkbox" data-group="check1" class="multicheck" value="1"><span></span></span></td>
				<td>
					<h4>Hello World!</h4>
					<ul class="list-inline separate hover-tools text sm">
						<li><a href="admin/posts/edit/1">Edit</a></li>
						<li><a href="#quick-edit" data-id="1">Quick Edit</a></li>
						<li><a href="#preview" data-id="1">Preview</a></li>
						<li><a href="#trash" class="danger" data-id="1">Trash</a></li>
					</ul>
				</td>
				<td>
					<p>This is your first blog post. Edit it or delete it, and begin writing!</p>
					<ul class="list-inline no-margin separate">
						<li>v 1.0</li>
						<li>by <a href="https://www.google.com">Author</a></li>
						<li><a href="https://www.google.com">Details</a></li>
					</ul>
				</td>
			</tr>
			<tr class="row-template hidden has-hover-tools">
				<td><span class="checkbox"><input type="checkbox" data-group="check1" class="multicheck" value="{{id}}"><span></span></span></td>
				<td>
					<h4>{{postTitle}}</h4>
					<ul class="list-inline separate hover-tools text sm">
						<li><a href="admin/posts/edit/{{id}}">Edit</a></li>
						<li><a href="#quick-edit" data-id="{{id}}">Quick Edit</a></li>
						<li><a href="#preview" data-id="{{id}}">Preview</a></li>
						<li><a href="#trash" class="danger" data-id="{{id}}">Trash</a></li>
					</ul>
				</td>
				<td>
					<p>{{description}}</p>
					<p class="no-margin">{{}} | {{author}}</p>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<th><span class="checkbox"><input type="checkbox" data-group="check1" class="multicheck all" value=""><span></span></span></th>
				<th>Plugin</th>
				<th>Description</th>
			</tr>
		</tfoot>
	</table>
