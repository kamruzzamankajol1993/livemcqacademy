@extends('admin.master.master')

@section('title') Create MCQ | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single { height: 38px; line-height: 38px; }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #4e73df; border: none; color: white; padding: 2px 8px;
    }
    .image-preview { width: 100px; height: 60px; object-fit: cover; border-radius: 5px; display: none; margin-top: 5px; border: 1px solid #ddd; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Create New MCQ</h2>
            <a href="{{ route('mcq.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left me-1"></i> Back to List</a>
        </div>

        <form action="{{ route('mcq.store') }}" method="POST" enctype="multipart/form-data" class="card shadow">
            @csrf
            <div class="card-body">
                @include('flash_message')

                {{-- Section 1: Classification & Type --}}
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">MCQ Content Type <span class="text-danger">*</span></label>
                        <select name="mcq_type" id="mcq_type" class="form-control border-primary" required>
                            <option value="text">Text Based (Standard)</option>
                            <option value="image">Image Based (Math/Graphics)</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-control select2" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->english_name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Class <span class="text-danger">*</span></label>
                        <select name="class_id" id="class_id" class="form-control select2" required>
                            <option value="">Select Class</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label>Department</label>
                        <select name="class_department_id" id="department_id" class="form-control select2">
                            <option value="">Select Department</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Section (New)</label>
                        <select name="section_id" id="section_id" class="form-control select2">
                            <option value="">Select Section</option>
                            @foreach($sections as $sec) <option value="{{ $sec->id }}">{{ $sec->name_en }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-control select2" required>
                            <option value="">Select Subject</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Chapter</label>
                        <select name="chapter_id" id="chapter_id" class="form-control select2">
                            <option value="">Select Chapter</option>
                        </select>
                    </div>
                </div>

                {{-- Section 2: Administrative Info (Multiple Selection) --}}
                <div class="row mb-4 p-3 bg-light rounded border">
                    <h6 class="text-primary mb-3"><i class="fa fa-university me-1"></i> Multi-Linked Data</h6>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Institutes (Multiple)</label>
                        <select name="institute_ids[]" id="institute_ids" class="form-control select2" multiple="multiple">
                            @foreach($institutes as $ins) <option value="{{ $ins->id }}">{{ $ins->name_en }}</option> @endforeach
                        </select>
                        <small class="text-muted">Select one or more institutes where this question appeared.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Boards (Multiple)</label>
                        <select name="board_ids[]" id="board_ids" class="form-control select2" multiple="multiple">
                            @foreach($boards as $brd) <option value="{{ $brd->id }}">{{ $brd->name_en }}</option> @endforeach
                        </select>
                        <small class="text-muted">Select boards associated with this MCQ.</small>
                    </div>
                </div>

                <hr>

                {{-- Section 3: Question & Options (Dynamic Toggle) --}}
                
                {{-- --- TEXT BASED FIELDS --- --}}
                <div id="text_fields">
                    <div class="mb-3">
                        <label class="fw-bold">Question Statement (Text)</label>
                        <textarea name="question" class="form-control summernote"></textarea>
                    </div>
                    <div class="row">
                        @for($i=1; $i<=4; $i++)
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <input class="form-check-input" type="radio" name="answer" value="{{ $i }}">
                                </span>
                                <input type="text" name="option_{{ $i }}" class="form-control" placeholder="Option {{ $i }} Text">
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- --- IMAGE BASED FIELDS --- --}}
                <div id="image_fields" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="fw-bold text-danger">Question Image</label>
                            <input type="file" name="question_img" class="form-control img-input" accept="image/*">
                            <img src="" class="image-preview">
                        </div>
                    </div>
                    <div class="row">
                        @for($i=1; $i<=4; $i++)
                        <div class="col-md-6 mb-3">
                            <div class="p-2 border rounded">
                                <label class="d-block">Option {{ $i }} Image 
                                    <input type="radio" name="answer_img_trigger" value="{{ $i }}" class="float-end">
                                </label>
                                <input type="file" name="option_{{ $i }}_img" class="form-control img-input" accept="image/*">
                                <img src="" class="image-preview">
                            </div>
                        </div>
                        @endfor
                    </div>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Note: When using Image Mode, text fields will be ignored. Select the radio button next to the correct image option.
                    </div>
                </div>

                <hr>

                {{-- Section 4: Metadata --}}
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label>Tags (Comma Separated)</label>
                        <select name="tags[]" class="form-control select2-tags" multiple="multiple"></select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label>Explanation / Short Description</label>
                        <textarea name="short_description" class="form-control" rows="3" placeholder="Explain the correct answer..."></textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end bg-light">
                <button type="reset" class="btn btn-warning me-2">Reset Form</button>
                <button type="submit" class="btn btn-primary btn-lg px-5"><i class="fa fa-save me-1"></i> Save MCQ</button>
            </div>
        </form>
    </div>
</main>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({ width: '100%' });
        $('.select2-tags').select2({ tags: true, tokenSeparators: [','] });
        $('.summernote').summernote({ height: 150 });

        // AJAX Routes (Ensure these match your web.php)
        var routes = {
            classes: "{{ route('mcq.ajax.classes') }}",
            departments: "{{ route('mcq.ajax.departments') }}",
            subjects: "{{ route('mcq.ajax.subjects') }}",
            chapters: "{{ route('mcq.ajax.chapters') }}"
        };

        // --- Toggle MCQ Type (Text vs Image) ---
        $('#mcq_type').on('change', function() {
            if($(this).val() === 'image') {
                $('#image_fields').slideDown();
                $('#text_fields').slideUp();
            } else {
                $('#image_fields').slideUp();
                $('#text_fields').slideDown();
            }
        });

        // --- Image Preview Logic ---
        $('.img-input').on('change', function() {
            var file = this.files[0];
            var preview = $(this).next('.image-preview');
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) { preview.attr('src', e.target.result).show(); }
                reader.readAsDataURL(file);
            }
        });

        // --- Dependent Dropdowns (AJAX) ---
        
        // 1. Category -> Class
        $('#category_id').on('change', function() {
            var id = $(this).val();
            $('#class_id').html('<option value="">Loading...</option>');
            if(id) {
                $.get(routes.classes, { category_id: id }, function(res) {
                    var ops = '<option value="">Select Class</option>';
                    res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                    $('#class_id').html(ops);
                });
            }
        });

        // 2. Class -> Department & Subject
        $('#class_id').on('change', function() {
            var clsId = $(this).val();
            if(clsId) {
                // Load Departments
                $.get(routes.departments, { class_id: clsId }, function(res) {
                    var ops = '<option value="">Select Department</option>';
                    res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                    $('#department_id').html(ops);
                });
                // Load Subjects
                loadSubjects(clsId, null);
            }
        });

        // 3. Department -> Reload Subject
        $('#department_id').on('change', function() {
            loadSubjects($('#class_id').val(), $(this).val());
        });

        function loadSubjects(clsId, deptId) {
            $('#subject_id').html('<option value="">Loading...</option>');
            $.get(routes.subjects, { class_id: clsId, department_id: deptId }, function(res) {
                var ops = '<option value="">Select Subject</option>';
                res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                $('#subject_id').html(ops);
            });
        }

        // 4. Subject -> Chapter
        $('#subject_id').on('change', function() {
            var subId = $(this).val();
            var clsId = $('#class_id').val();
            if(subId) {
                $.get(routes.chapters, { subject_id: subId, class_id: clsId }, function(res) {
                    var ops = '<option value="">Select Chapter</option>';
                    res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                    $('#chapter_id').html(ops);
                });
            }
        });
    });
</script>
@endsection