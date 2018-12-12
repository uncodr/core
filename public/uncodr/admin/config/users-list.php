		<div class="page-heading">
			<input type="text" name="search" placeholder="Search" class="search">
			<h1 class="dropdown">
				<a href="admin/config/users" class="default"><?= $heading; ?> <i class="ion ion-arrow-down-b"></i></a>
				<ul class="content list-group text-default">
					<li><a href="admin/config/users">Users</a></li>
					<li><a href="admin/config/groups">Groups</a></li>
				</ul>
			</h1>
			<a href="admin/config/users#new" class="btn sm">Add New</a>
		</div>
		<ul class="list-inline toolbar">
			<li class="btn-group bulk-axn hidden">
				<a class="btn btn-red" data-action="trash"><i class="ion ion-trash-b"></i></a>
			</li>
			<li class="btn-group filters">
			</li>
			<li class="right pagination hidden">
				<input type="text" name="page-num" value=""> of <span class="bold page-count"></span>
				<div class="btn-group">
					<a class="btn btn-default first" data-page=""><i class="ion ion-skip-backward"></i></a>
					<a class="btn btn-default prev" data-page=""><i class="ion ion-arrow-left-b"></i></a>
					<a class="btn btn-default next" data-page=""><i class="ion ion-arrow-right-b"></i></a>
					<a class="btn btn-default last" data-page=""><i class="ion ion-skip-forward"></i></a>
				</div>
			</li>
		</ul>
		<div class="search-title hidden"></div>
		<table class="panel multi-row hover tabl">
			<thead>
				<tr>
					<th class="width-min"><span class="checkbox"><input type="checkbox" data-axn="bulk-axn" class="multicheck all" value=""><span></span></span></th>
					<th class="width-40 bold">Login Details</th>
					<th class="width-40">Name <span class="crafty">&amp;</span> Created On</th>
					<th class="width-20">Groups</th>
					<th class="width-20 text-center">Last Login</th>
				</tr>
			</thead>
			<tbody id="user-list">
				<tr class="placeholder">
					<td></td>
					<td colspan="5">No users found</td>
				</tr>
				<tr class="template hidden">
					<td><span class="checkbox"><input type="checkbox" class="multicheck" value="{{id}}"><span></span></span></td>
					<td><a href="admin/config/users#id:{{id}}" class="bold">{{email}}<br>{{login}}</a>{{emailVerified}}</td>
					<td>{{name}}<br><span class="semi-lite">{{addedOn}}</span></td>
					<td>{{groups}}</td>
					<td class="text-center">{{lastLogin}}<br><span class="prompt">{{loginCount}}</span></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th><span class="checkbox"><input type="checkbox" data-axn="bulk-axn" class="multicheck all" value=""><span></span></span></th>
					<th class="bold">Login Details</th>
					<th>Name <span class="crafty">&amp;</span> Created On</th>
					<th>Groups</th>
					<th class="text-center">Last Login</th>
				</tr>
			</tfoot>
		</table>
