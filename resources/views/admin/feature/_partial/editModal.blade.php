<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Feature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">English Name <span class="text-danger">*</span></label>
                            <input type="text" name="english_name" id="edit_english_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bangla Name <span class="text-danger">*</span></label>
                            <input type="text" name="bangla_name" id="edit_bangla_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Feature</label>
                            <select name="parent_id" id="edit_parent_id" class="form-control">
                                <option value="">None</option>
                                @foreach($allFeatures as $pFeat)
                                    <option value="{{ $pFeat->id }}">{{ $pFeat->english_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- EDIT COLOR INPUT SECTION --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Color (Hex, RGB or Gradient) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="color" id="edit_color" class="form-control" required>
                                <input type="color" class="form-control form-control-color" style="max-width: 50px;" onchange="updateColorInput(this, 'edit_color', 'edit_color_preview')">
                            </div>
                            <div id="edit_color_preview" class="color-preview-box"></div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_description" id="edit_short_description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
    <label class="form-label">Change Image (80x80px)</label>
    
    <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'edit_image_preview')">
    
    <div class="mt-2">
        <img id="edit_image_preview" src="" style="width: 80px; height: 80px; display: none;" class="img-thumbnail">
    </div>
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
                        <button type="submit" class="btn btn-primary">Update Feature</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>