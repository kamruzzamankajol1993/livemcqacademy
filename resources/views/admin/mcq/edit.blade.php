@extends('admin.master.master')

@section('title') Edit MCQ | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single { height: 38px; line-height: 38px; }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #4e73df; border: none; color: white; padding: 2px 8px;
    }
    .image-preview-wrapper { position: relative; display: inline-block; margin-top: 10px; }
    .image-preview { width: 150px; height: auto; border: 2px solid #eaecf4; border-radius: 8px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); }
    .badge-type { position: absolute; top: -10px; right: -10px; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Edit MCQ</h2>
                <small class="text-muted">Update question details for ID: #{{ $mcq->id }}</small>
            </div>
            <a href="{{ route('mcq.index') }}" class="btn btn-secondary shadow-sm"><i class="fa fa-arrow-left me-1"></i> Back to List</a>
        </div>

        <form action="{{ route('mcq.update', $mcq->id) }}" method="POST" enctype="multipart/form-data" class="card shadow border-0">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('flash_message')

                {{-- Section 1: Classification & Type --}}
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">MCQ Content Type <span class="text-danger">*</span></label>
                        <select name="mcq_type" id="mcq_type" class="form-control border-primary" required>
                            <option value="text" {{ $mcq->mcq_type == 'text' ? 'selected' : '' }}>Text Based</option>
                            <option value="image" {{ $mcq->mcq_type == 'image' ? 'selected' : '' }}>Image Based</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-control select2" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat) 
                                <option value="{{ $cat->id }}" {{ $mcq->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->english_name }}</option> 
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Class <span class="text-danger">*</span></label>
                        <select name="class_id" id="class_id" class="form-control select2" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $cls)
                                <option value="{{ $cls->id }}" {{ $mcq->class_id == $cls->id ? 'selected' : '' }}>{{ $cls->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label>Department</label>
                        <select name="class_department_id" id="department_id" class="form-control select2">
                            <option value="">Select Department</option>
                            {{-- JS will load this --}}
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Section</label>
                        <select name="section_id" id="section_id" class="form-control select2">
                            <option value="">Select Section</option>
                            @foreach($sections as $sec) 
                                <option value="{{ $sec->id }}" {{ $mcq->section_id == $sec->id ? 'selected' : '' }}>{{ $sec->name_en }}</option> 
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id" class="form-control select2" required>
                            <option value="">Select Subject</option>
                            {{-- JS will load this --}}
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Chapter</label>
                        <select name="chapter_id" id="chapter_id" class="form-control select2">
                            <option value="">Select Chapter</option>
                            {{-- JS will load this --}}
                        </select>
                    </div>
                </div>

                {{-- Section 2: Administrative Info (Multi-Linked) --}}
                <div class="row mb-4 p-3 bg-light rounded border">
                    <h6 class="text-primary mb-3"><i class="fa fa-university me-1"></i> Linked Institutions & Boards</h6>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Institutes (Multiple)</label>
                        <select name="institute_ids[]" id="institute_ids" class="form-control select2" multiple="multiple">
                            @foreach($institutes as $ins) 
                                <option value="{{ $ins->id }}" {{ in_array($ins->id, (array)$mcq->institute_ids) ? 'selected' : '' }}>{{ $ins->name_en }}</option> 
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Boards (Multiple)</label>
                        <select name="board_ids[]" id="board_ids" class="form-control select2" multiple="multiple">
                            @foreach($boards as $brd) 
                                <option value="{{ $brd->id }}" {{ in_array($brd->id, (array)$mcq->board_ids) ? 'selected' : '' }}>{{ $brd->name_en }}</option> 
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr>

                {{-- Section 3: Question & Options (Dynamic Toggle) --}}
                
                {{-- --- TEXT BASED FIELDS --- --}}
                <div id="text_fields" style="{{ $mcq->mcq_type == 'text' ? '' : 'display: none;' }}">
                    <div class="mb-3">
                        <label class="fw-bold">Question Statement (Text)</label>
                        <textarea name="question" class="form-control summernote">{{ $mcq->question }}</textarea>
                    </div>
                    <div class="row">
                        @for($i=1; $i<=4; $i++)
                        @php $opt = 'option_'.$i; @endphp
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <input class="form-check-input" type="radio" name="answer" value="{{ $i }}" {{ $mcq->answer == $i ? 'checked' : '' }}>
                                </span>
                                <input type="text" name="{{ $opt }}" class="form-control" value="{{ $mcq->$opt }}" placeholder="Option {{ $i }} Text">
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- --- IMAGE BASED FIELDS --- --}}
                <div id="image_fields" style="{{ $mcq->mcq_type == 'image' ? '' : 'display: none;' }}">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="fw-bold">Question Image</label>
                            @if($mcq->question_img)
                                <div class="mb-2">
                                    <img src="{{ asset($mcq->question_img) }}" class="image-preview border-danger" style="display:block;">
                                    <small class="text-danger">Current Image</small>
                                </div>
                            @endif
                            <input type="file" name="question_img" class="form-control img-input" accept="image/*">
                        </div>
                    </div>
                    <div class="row">
                        @for($i=1; $i<=4; $i++)
                        @php $optImg = 'option_'.$i.'_img'; @endphp
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded bg-white">
                                <label class="d-block fw-bold">Option {{ $i }} Image 
                                    <input type="radio" name="answer_img_trigger" value="{{ $i }}" {{ $mcq->answer == $i ? 'checked' : '' }} class="float-end">
                                </label>
                                @if($mcq->$optImg)
                                    <div class="mb-2">
                                        <img src="{{ asset($mcq->$optImg) }}" class="image-preview" style="display:block;">
                                    </div>
                                @endif
                                <input type="file" name="{{ $optImg }}" class="form-control img-input" accept="image/*">
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>

                <hr>

                {{-- Section 4: Metadata --}}
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label>Tags</label>
                        <select name="tags[]" class="form-control select2-tags" multiple="multiple">
                            @if($mcq->tags)
                                @foreach($mcq->tags as $tag)
                                    <option value="{{ $tag }}" selected>{{ $tag }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ $mcq->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $mcq->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label>Explanation / Short Description</label>
                        <textarea name="short_description" class="form-control" rows="3">{{ $mcq->short_description }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm"><i class="fa fa-save me-1"></i> Update MCQ</button>
            </div>
        </form>
    </div>
</main>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });
        $('.select2-tags').select2({ tags: true, tokenSeparators: [','] });
        $('.summernote').summernote({ height: 150 });

        var routes = {
            classes: "{{ route('mcq.ajax.classes') }}",
            departments: "{{ route('mcq.ajax.departments') }}",
            subjects: "{{ route('mcq.ajax.subjects') }}",
            chapters: "{{ route('mcq.ajax.chapters') }}"
        };

        // Saved IDs for pre-filling
        var saved = {
            cls: "{{ $mcq->class_id }}",
            dept: "{{ $mcq->class_department_id }}",
            sub: "{{ $mcq->subject_id }}",
            chap: "{{ $mcq->chapter_id }}"
        };

        // --- Toggle MCQ Type ---
        $('#mcq_type').on('change', function() {
            if($(this).val() === 'image') {
                $('#image_fields').fadeIn();
                $('#text_fields').hide();
            } else {
                $('#image_fields').hide();
                $('#text_fields').fadeIn();
            }
        });

        // --- Dependent Dropdowns (Initialization & Events) ---

        function loadDepartments(clsId, selected = null) {
            $.get(routes.departments, { class_id: clsId }, function(res) {
                var ops = '<option value="">Select Department</option>';
                res.forEach(el => ops += `<option value="${el.id}" ${el.id == selected ? 'selected' : ''}>${el.name_en}</option>`);
                $('#department_id').html(ops);
            });
        }

        function loadSubjects(clsId, deptId, selected = null) {
            $.get(routes.subjects, { class_id: clsId, department_id: deptId }, function(res) {
                var ops = '<option value="">Select Subject</option>';
                res.forEach(el => ops += `<option value="${el.id}" ${el.id == selected ? 'selected' : ''}>${el.name_en}</option>`);
                $('#subject_id').html(ops);
                if(selected) loadChapters(selected, clsId, saved.chap);
            });
        }

        function loadChapters(subId, clsId, selected = null) {
            $.get(routes.chapters, { subject_id: subId, class_id: clsId }, function(res) {
                var ops = '<option value="">Select Chapter</option>';
                res.forEach(el => ops += `<option value="${el.id}" ${el.id == selected ? 'selected' : ''}>${el.name_en}</option>`);
                $('#chapter_id').html(ops);
            });
        }

        // Init
        if(saved.cls) {
            loadDepartments(saved.cls, saved.dept);
            loadSubjects(saved.cls, saved.dept, saved.sub);
        }

        // Change Events
        $('#category_id').on('change', function() {
            var id = $(this).val();
            $.get(routes.classes, { category_id: id }, function(res) {
                var ops = '<option value="">Select Class</option>';
                res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                $('#class_id').html(ops);
            });
        });

        $('#class_id').on('change', function() {
            var clsId = $(this).val();
            loadDepartments(clsId);
            loadSubjects(clsId, null);
        });

        $('#department_id').on('change', function() {
            loadSubjects($('#class_id').val(), $(this).val());
        });

        $('#subject_id').on('change', function() {
            loadChapters($(this).val(), $('#class_id').val());
        });
    });
</script>
@endsection