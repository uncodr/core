<div class="page-heading">
    <h1><?= $heading; ?></h1>
</div>
<ul class="toolbar list-inline">
   <li><a href="admin/plugins/tags#new"class="btn btn-add" title="Add Tag"><i class="ion ion-plus-round"></i></a></li>
   <li class="right pagination tags-paginate hidden"></li>
</ul>

<!---------------- Add/Edit Form Start ---------------------------------------->
<form class="add-edit hidden">
    <input type="hidden" name="id" value="">
    <input type="text" name="tag_name" value="" placeholder="Tag Name" class="full" required>
    <select name="tag_type" class="select full" required>
        <option value="">Select</option>
        <option value="section">Section</option>
        <option value="concept">Concept</option>
        <option value="topic">Topic</option>
    </select>
    <input type="text" name="related_to" value="" placeholder="Related To" class="full" required>
    <input type="text" name="description" value="" placeholder="Tag Description" class="full" required><br>
    <ul class="btn-group list-inline">
        <li><a class="btn btn-save">Save</a></li>
        <li><a class="btn btn-default btn-cancel">Cancel</a></li>
    </ul>
</form>
<!---------------- Add/Edit Form End ------------------------------------------>

<!--------------- Show Tags Start --------------------------------------------->
<table class="panel multi-row hover tabl tagslist">
    <thead>
        <tr>
            <th>TagID</th>
            <th>TagName</th>
            <th>TagType</th>
            <th>RelatedTo</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody class="tagslist-body">
        <tr class="template hidden">
            <td>{{id}}</td>
            <td>{{tagName}}</td>
            <td>{{tagType}}</td>
            <td>{{relatedTo}}</td>
            <td>{{description}}</td>
            <td>
                <a class="btn btn-edit" title="Edit Tag" data-id="{{id}}"><i class="ion ion-edit"></i></a>
                <a class="btn btn-red btn-delete" title="Delete Tag" data-id="{{id}}"><i class="ion ion-trash-a"></i></a>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th>TagID</th>
            <th>TagName</th>
            <th>TagType</th>
            <th>RelatedTo</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </tfoot>
</table>
<!--------------- Show Tags End ----------------------------------------------->
<ul class="toolbar list-inline">
   <li class="right pagination tags-paginate hidden"></li>
</ul>
