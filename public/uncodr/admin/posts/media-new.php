		<div class="page-heading">
			<h1>Add <?= $heading; ?></h1>
		</div>
		<ul class="list-inline toolbar">
			<li class="btn-group">
				<a class="btn btn-default active tab-links" data-group=".tab-links" data-target=".tab-wrapper:.uploader"><i class="ion ion-upload"></i></a>
				<a class="btn btn-default tab-links" data-group=".tab-links" data-target=".tab-wrapper:label.content"><i class="ion ion-code"></i></a>
				<a class="btn btn-default tab-links" data-group=".tab-links" data-target=".tab-wrapper:.external"><i class="ion ion-link"></i></a>
				<a class="btn sep btn-save">Save</a>
			</li>
			<li>
				<a href="admin/posts/media#type:all" class="btn btn-default danger">Cancel</a>
			</li>
		</ul>
		<form class="row">
			<div class="col sm-8 s-6">
				<div class="tab-wrapper uploader">
					<span class="label">Upload File</span>
					<div class="dropzone"></div>
				</div>
				<label class="tab-wrapper content hidden">
					<span class="field-name">Content <span class="lite">(svg code, binary text, etc.)</span></span>
					<span class="aspect-fx a5-2"><textarea name="content"></textarea></span>
				</label>
			</div>
			<div class="col sm-4 s-6">
				<label>
					<span class="field-name">Title</span>
					<input type="text" name="title" value="" class="big full" required>
				</label>
				<label>
					<span class="field-name">Description</span>
					<textarea name="description" class="full"></textarea>
				</label>
			</div>
		</form>
