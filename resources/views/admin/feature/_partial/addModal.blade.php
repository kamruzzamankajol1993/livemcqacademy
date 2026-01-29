<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Feature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('feature.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">English Name <span class="text-danger">*</span></label>
                            <input type="text" name="english_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bangla Name <span class="text-danger">*</span></label>
                            <input type="text" name="bangla_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Feature</label>
                            <select name="parent_id" class="form-control">
                                <option value="">None</option>
                                @foreach($allFeatures as $pFeat)
                                    <option value="{{ $pFeat->id }}">{{ $pFeat->english_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- COLOR INPUT SECTION --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Color (Hex, RGB or Gradient) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                {{-- Text Input for Gradient/Hex --}}
                                <input type="text" name="color" id="add_color_input" class="form-control" placeholder="e.g. #ff0000 or linear-gradient(...)" required>
                                {{-- Color Picker Helper --}}
                                <input type="color" class="form-control form-control-color" style="max-width: 50px;" onchange="updateColorInput(this, 'add_color_input', 'add_color_preview')">
                            </div>
                            {{-- Live Preview Box --}}
                            <div id="add_color_preview" class="color-preview-box"></div>
                            <small class="text-muted" style="font-size: 11px;">Paste Hex code, RGB, or CSS Gradient code.</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
    <label class="form-label">Image (80x80px) <span class="text-danger">*</span></label>
    
    <input type="file" name="image" class="form-control" accept="image/*" required onchange="previewImage(this, 'add_image_preview')">
    
    <div class="mt-2">
        <img id="add_image_preview" src="#" alt="Image Preview" style="display: none; width: 80px; height: 80px;" class="img-thumbnail">
    </div>
</div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Feature</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>