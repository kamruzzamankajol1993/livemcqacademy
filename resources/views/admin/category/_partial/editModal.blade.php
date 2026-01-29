<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Feature</label>
                            <select name="feature_id" id="edit_feature_id" class="form-control select2-modal" style="width: 100%;">
                                <option value="">-- No Feature --</option>
                                @foreach($features as $feat)
                                    <option value="{{ $feat->id }}">{{ $feat->english_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" id="edit_parent_id" class="form-control select2-modal" style="width: 100%;">
                                <option value="">-- None --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->english_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">English Name <span class="text-danger">*</span></label>
                            <input type="text" name="english_name" id="edit_english_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bangla Name <span class="text-danger">*</span></label>
                            <input type="text" name="bangla_name" id="edit_bangla_name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Color</label>
                            <div class="input-group">
                                <input type="text" name="color" id="edit_color" class="form-control">
                                <input type="color" class="form-control form-control-color" style="max-width: 50px;" onchange="updateColorInput(this, 'edit_color', 'edit_color_preview')">
                            </div>
                            <div id="edit_color_preview" class="color-preview-box"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" onchange="previewImage(this, 'edit_image_preview')">
                            <div class="mt-2"><img id="edit_image_preview" src="" style="display:none; width:50px; height:50px;" class="img-thumbnail"></div>
                        </div>

                         <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>