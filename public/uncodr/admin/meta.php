		<div class="page-heading">
			<h1>Meta Data</h1>
			<a class="btn btn-add sm">Add New</a>
		</div>
		<table class="panel hover meta-table">
			<thead>
				<tr>
					<th>Key</th>
					<th>Value</th>
					<th class="relative"><a class="btn btn-save hidden">Save</a></th>
				</tr>
			</thead>
			<tbody>
				<tr class="template hidden">
					<td class="field bold" data-name="key"></td>
					<td><span class="field hellip" data-name="value"></span></td>
					<td><span class="btn-group">
						<a class="btn-edit btn btn-default" title="Edit"><i class="ion ion-edit"></i></a>
						<a class="btn-copy btn btn-default" title="Duplicate"><i class="ion ion-ios-copy-outline"></i></a>
						<a class="btn-delete btn btn-default danger" title="Delete"><i class="ion ion-trash-a"></i></a>
					</span></td>
				</tr>
				<tr class="template new-meta hidden">
					<td>
						<span class="select full"><select name="key_dd"></select><span></span></span>
						<input type="text" class="full hidden" name="key" value="" required>
					</td>
					<td>
						<span class="select full"><select name="type">
							<option value="">Select a type</option>
							<option value="obj">Object</option>
							<option value="arr">Array</option>
							<option value="bool">Boolean</option>
							<option value="int">Integer</option>
							<option value="num">Float</option>
							<option value="str">Text</option>
						</select><span></span></span>
						<input type="text" class="full hidden" name="value">
						<span class="checkbox hidden"><input type="checkbox" name="value" value="1"><span></span></span>
					</td>
					<td><span class="btn-group">
						<a class="btn-done btn btn-default" title="Save"><i class="ion ion-checkmark-round"></i></a>
						<a class="btn-cancel btn btn-default" title="Cancel"><i class="ion ion-close-round"></i></a>
					</span></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th>Key</th>
					<th>Value</th>
					<th><a class="btn sm btn-save-all hidden pull-right">Save All</a></th>
				</tr>
			</tfoot>
		</table>
