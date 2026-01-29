@extends('admin.master.master')

@section('title') Create MCQ | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single { height: 38px; line-height: 38px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    .note-editor.note-frame { border: 1px solid #ced4da; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Create MCQ</h2>
            <a href="{{ route('mcq.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left me-1"></i> Back to List</a>
        </div>

        <form action="{{ route('mcq.store') }}" method="POST" class="card">
            @csrf
            <div class="card-body">
                @include('flash_message')

                {{-- Section 1: Academic Information --}}
                <div class="row mb-4 p-3 bg-light rounded border">
                    <h6 class="text-primary mb-3"><i class="fa fa-university me-1"></i> Academic Info (Optional)</h6>
                    
                    {{-- New: Institute Type --}}
                    <div class="col-md-3 mb-3">
                        <label>Institute Type</label>
                        <select id="institute_type" class="form-control select2">
                            <option value="">Select Type</option>
                            <option value="school">School</option>
                            <option value="college">College</option>
                            <option value="university">University</option>
                        </select>
                    </div>

                    {{-- Institute (Loads via AJAX based on Type) --}}
                    <div class="col-md-3 mb-3">
                        <label>Institute</label>
                        <select name="institute_id" id="institute_id" class="form-control select2">
                            <option value="">Select Institute</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Board</label>
                        <select name="board_id" class="form-control select2">
                            <option value="">Select Board</option>
                            @foreach($boards as $b) <option value="{{ $b->id }}">{{ $b->name_en }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Academic Year</label>
                        <select name="year_id" class="form-control select2">
                            <option value="">Select Year</option>
                            @foreach($years as $y) <option value="{{ $y->id }}">{{ $y->name_en }}</option> @endforeach
                        </select>
                    </div>
                </div>

                {{-- Section 2: Question Hierarchy (Dependent Dropdowns) --}}
                <div class="row mb-3">
                    <div class="col-md-3 mb-3">
                        <label>Category *</label>
                        <select name="category_id" id="category_id" class="form-control select2" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name_en }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Class *</label>
                        <select name="class_id" id="class_id" class="form-control select2" required>
                            <option value="">Select Class</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Department (Optional)</label>
                        <select name="class_department_id" id="department_id" class="form-control select2">
                            <option value="">Select Department</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Subject *</label>
                        <select name="subject_id" id="subject_id" class="form-control select2" required>
                            <option value="">Select Subject</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Chapter</label>
                        <select name="chapter_id" id="chapter_id" class="form-control select2">
                            <option value="">Select Chapter</option>
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label>Topic</label>
                        <select name="topic_id" id="topic_id" class="form-control select2">
                            <option value="">Select Topic</option>
                        </select>
                    </div>
                </div>

                <hr>

                {{-- Section 3: Question & Options --}}
                <div class="mb-3">
                    <label class="fw-bold">Question *</label>
                    <textarea name="question" class="form-control summernote" required></textarea>
                </div>

                <div class="row">
                    @for($i=1; $i<=4; $i++)
                    <div class="col-md-6 mb-3">
                        <div class="input-group">
                            <div class="input-group-text bg-white">
                                <input class="form-check-input mt-0" type="radio" name="answer" value="{{ $i }}" required aria-label="Correct Answer">
                            </div>
                            <input type="text" name="option_{{ $i }}" class="form-control" placeholder="Option {{ $i }}" required>
                        </div>
                    </div>
                    @endfor
                    <small class="text-muted mb-3 d-block ps-3">* Click the radio button to select the correct answer.</small>
                </div>

                {{-- Section 4: Metadata (Upload Type Removed) --}}
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label>Tags</label>
                        <select name="tags[]" class="form-control select2" multiple="multiple">
                            {{-- Users can type new tags --}}
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label>Short Description / Explanation</label>
                        <textarea name="short_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Save MCQ</button>
            </div>
        </form>
    </div>
</main>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ tags: true, tokenSeparators: [',', ' '] });
        $('.summernote').summernote({ height: 150 });

        var routes = {
            institutes: "{{ route('mcq.ajax.institutes') }}",
            classes: "{{ route('mcq.ajax.classes') }}",
            departments: "{{ route('mcq.ajax.departments') }}",
            subjects: "{{ route('mcq.ajax.subjects') }}",
            chapters: "{{ route('mcq.ajax.chapters') }}",
            topics: "{{ route('mcq.ajax.topics') }}",
        };

        // --- 1. Institute Type -> Institute ---
        $('#institute_type').on('change', function() {
            var type = $(this).val();
            $('#institute_id').html('<option value="">Loading...</option>');
            
            if(type) {
                $.get(routes.institutes, { type: type }, function(res) {
                    var ops = '<option value="">Select Institute</option>';
                    res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                    $('#institute_id').html(ops);
                });
            } else {
                $('#institute_id').html('<option value="">Select Institute</option>');
            }
        });

        // --- 2. Category -> Class ---
        $('#category_id').on('change', function() {
            var id = $(this).val();
            $('#class_id').html('<option value="">Loading...</option>');
            // Reset downstream
            $('#department_id').html('<option value="">Select Department</option>');
            $('#subject_id').html('<option value="">Select Subject</option>');
            $('#chapter_id').html('<option value="">Select Chapter</option>');
            $('#topic_id').html('<option value="">Select Topic</option>');

            if(id) {
                $.get(routes.classes, { category_id: id }, function(res) {
                    var ops = '<option value="">Select Class</option>';
                    res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                    $('#class_id').html(ops);
                });
            } else {
                 $('#class_id').html('<option value="">Select Class</option>');
            }
        });

        // --- 3. Class -> Department AND Subject ---
        $('#class_id').on('change', function() {
            var clsId = $(this).val();
            
            if(clsId) {
                // Load Departments
                $('#department_id').html('<option value="">Loading...</option>');
                $.get(routes.departments, { class_id: clsId }, function(res) {
                    var ops = '<option value="">Select Department</option>';
                    res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                    $('#department_id').html(ops);
                });

                // Load Subjects (Initially based on Class only)
                loadSubjects(clsId, null);
            } else {
                $('#department_id').html('<option value="">Select Department</option>');
                $('#subject_id').html('<option value="">Select Subject</option>');
            }
        });

        // --- 4. Department -> Reload Subject ---
        $('#department_id').on('change', function() {
            var deptId = $(this).val();
            var clsId = $('#class_id').val();
            loadSubjects(clsId, deptId);
        });

        function loadSubjects(clsId, deptId) {
            $('#subject_id').html('<option value="">Loading...</option>');
            $.get(routes.subjects, { class_id: clsId, department_id: deptId }, function(res) {
                var ops = '<option value="">Select Subject</option>';
                res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                $('#subject_id').html(ops);
            });
        }

        // --- 5. Subject -> Chapter (Depends on Subject AND Class) ---
        $('#subject_id').on('change', function() {
            var subId = $(this).val();
            var clsId = $('#class_id').val();

            if(subId) {
                $('#chapter_id').html('<option value="">Loading...</option>');
                $.get(routes.chapters, { subject_id: subId, class_id: clsId }, function(res) {
                    var ops = '<option value="">Select Chapter</option>';
                    res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                    $('#chapter_id').html(ops);
                });
            } else {
                $('#chapter_id').html('<option value="">Select Chapter</option>');
            }
        });

        // --- 6. Chapter -> Topic ---
        $('#chapter_id').on('change', function() {
            var chapId = $(this).val();
            if(chapId) {
                $('#topic_id').html('<option value="">Loading...</option>');
                $.get(routes.topics, { chapter_id: chapId }, function(res) {
                    var ops = '<option value="">Select Topic</option>';
                    res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                    $('#topic_id').html(ops);
                });
            } else {
                $('#topic_id').html('<option value="">Select Topic</option>');
            }
        });
    });
</script>
@endsection