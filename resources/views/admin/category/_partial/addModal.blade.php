<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('category.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        {{-- Feature Selection --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Feature</label>
                            <select name="feature_id" class="form-control select2-modal" style="width: 100%;">
                                <option value="">-- No Feature --</option>
                                @foreach($features as $feat)
                                    <option value="{{ $feat->id }}">{{ $feat->english_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Parent Category --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Category</label>
                            <select name="parent_id" class="form-control select2-modal" style="width: 100%;">
                                <option value="">-- None --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->english_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Names --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">English Name <span class="text-danger">*</span></label>
                            <input type="text" name="english_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bangla Name <span class="text-danger">*</span></label>
                            <input type="text" name="bangla_name" class="form-control" required>
                        </div>

                        {{-- Color Input --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Color (Hex/Gradient)</label>
                            <div class="input-group">
                                <input type="text" name="color" id="add_color_input" class="form-control" placeholder="#ff0000 or gradient">
                                <input type="color" class="form-control form-control-color" style="max-width: 50px;" onchange="updateColorInput(this, 'add_color_input', 'add_color_preview')">
                            </div>
                            <div id="add_color_preview" class="color-preview-box"></div>
                        </div>

                        {{-- Image --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Image (50x50px)</label>
                            <input type="file" name="image" class="form-control" onchange="previewImage(this, 'add_image_preview')">
                            <div class="mt-2"><img id="add_image_preview" src="#" style="display:none; width:50px; height:50px;" class="img-thumbnail"></div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>