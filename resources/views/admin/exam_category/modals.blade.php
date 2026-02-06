<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('exam-category.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5>Add New Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Name (EN)</label><input type="text" name="name_en" class="form-control" required></div>
                <div class="mb-3"><label>Name (BN)</label><input type="text" name="name_bn" class="form-control"></div>
                <div class="mb-3"><label>Status</label><select name="status" class="form-control"><option value="1">Active</option><option value="0">Inactive</option></select></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editForm" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header bg-info text-white"><h5>Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Name (EN)</label><input type="text" name="name_en" id="edit_name_en" class="form-control" required></div>
                <div class="mb-3"><label>Name (BN)</label><input type="text" name="name_bn" id="edit_name_bn" class="form-control"></div>
                <div class="mb-3"><label>Status</label><select name="status" id="edit_status" class="form-control"><option value="1">Active</option><option value="0">Inactive</option></select></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-info text-white">Update</button></div>
        </form>
    </div>
</div>