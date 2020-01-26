		<div class="page-heading">
			<input type="text" name="search" placeholder="Search" class="search">
			<h1><?= $heading; ?></h1>
			<a href="admin/posts/<?= $subPage; ?>s#new" class="btn sm">Add New</a>
		</div>
		<ul class="list-inline toolbar">
			<li class="btn-group bulk-axn hidden">
				<a class="btn btn-default" data-action="edit"><i class="ion ion-edit"></i></a>
				<a class="btn sep btn-red" data-action="trash"><i class="ion ion-trash-b"></i></a>
			</li>
			<li class="btn-group filters"></li>
			<li class="right pagination hidden"></li>
		</ul>
		<div class="search-title hidden"></div>
		<table class="panel multi-row hover">
			<thead>
				<tr>
					<th class="width-min"><span class="checkbox"><input type="checkbox" data-axn="bulk-axn" class="multicheck all" value=""><span></span></span></th>
					<th>Title</th>
					<th class="width-16">Author</th>
					<th class="width-14">Date</th>
					<th class="width-6 text-center"><i class="ion ion-chatboxes"></i></th>
				</tr>
			</thead>
			<tbody>
				<tr class="placeholder hidden">
					<td></td>
					<td colspan="4">No <?= $subPage; ?>s found</td>
				</tr>
				<tr class="template hidden has-hover-tools">
					<td><span class="checkbox"><input type="checkbox" class="multicheck" value="{{id}}"><span></span></span></td>
					<td>
						<h4><a href="admin/posts/<?= $subPage; ?>s#id:{{id}}">{{title}}</a></h4>
						<p class="no-margin">{{excerpt}}</p>
						<ul class="list-inline separate hover-tools text sm">
							<li><a href="admin/posts/<?= $subPage; ?>s#meta:{{id}}">Edit Meta</a></li>
							<li><a class="btn-status-change" data-status="3" data-id="{{id}}">Publish</a></li>
							<li><a class="danger btn-status-change" data-status="0" data-id="{{id}}">Trash</a></li>
							<li><a class="danger btn-delete" data-id="{{id}}">Delete Forever</a></li>
						</ul>
					</td>
					<td><a href="admin/posts/<?= $subPage; ?>s#status:all/author:{{author}}">{{authorName}}</a></td>
					<td><span class="semi-bold">{{status}}</span><br><span class="inline-block">{{axnDate}}</span> <span class="block">{{axnTime}}</span></td>
					<td class="text-center"><span class="chat-bubble">{{commentCount}}</span></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th><span class="checkbox"><input type="checkbox" class="multicheck all" value=""><span></span></span></th>
					<th>Title</th>
					<th>Author</th>
					<th>Date</th>
					<th class="text-center"><i class="ion ion-chatboxes"></i></th>
				</tr>
			</tfoot>
		</table>
		<ul class="list-inline toolbar">
			<li class="btn-group bulk-axn hidden">
				<a class="btn btn-default" data-action="edit"><i class="ion ion-edit"></i></a>
				<a class="btn btn-default" data-action="trash"><i class="ion ion-trash-b"></i></a>
			</li>
			<li class="btn-group filters"></li>
			<li class="right pagination hidden"></li>
		</ul>
