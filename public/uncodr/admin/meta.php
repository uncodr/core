<div class="page-heading">
	<h1>Meta Data</h1>
</div>
<ul class="toolbar list-inline">
	<li><a class="btn btn-add" title="Add new record">Add New</a></li>
	<li><a class="btn btn-default danger btn-back">Back</a></li>
</ul>
<table class="panel hover meta-table">
	<thead>
		<tr>
			<th>Key</th>
			<th>Value</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody class="bold">
		<tr class="template hidden">
			<td>{{key}}</td>
			<td>{{value}}</td>
			<td><span class="btn-group">
				<a class="btn-edit btn btn-default" title="Edit"><i class="ion ion-edit"></i></a>
				<a class="btn-delete btn btn-red" title="Delete"><i class="ion ion-trash-a"></i></a>
			</span></td>
		</tr>
		<tr class="template new-meta hidden">
			<td>
				<span class="select full"><select name="key_dd"></select><span></span></span>
				<input type="text" class="full" name="key" value="" required>
			</td>
			<td>
				<input type="text" class="full" name="value" required>
			</td>
			<td><span class="btn-group">
				<a class="btn-save btn">Save</a>
				<a class="btn-cancel btn btn-default">Cancel</a>
			</span></td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<th>Key</th>
			<th>Value</th>
			<th>Actions</th>
		</tr>
	</tfoot>
</table>
