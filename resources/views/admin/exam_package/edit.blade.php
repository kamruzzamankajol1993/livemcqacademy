@extends('admin.master.master')
@section('title', 'Edit Exam Package')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-11 mx-auto">
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fa fa-edit me-2"></i>Edit Exam Package</h5>
                        <a href="{{ route('exam-package.index') }}" class="btn btn-sm btn-light">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('exam-package.update', $package->id) }}" method="POST">
                            @csrf 
                            @method('PUT')
                            
                            <div class="row">
                                {{-- Exam Name --}}
                                <div class="col-md-12 mb-3">
                                    <label class="fw-bold">Exam Name <span class="text-danger">*</span></label>
                                    <input type="text" name="exam_name" class="form-control" value="{{ $package->exam_name }}" required>
                                </div>

                                {{-- Class Selection --}}
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-control select2" required>
                                        <option value="">-- Select Class --</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ $package->class_id == $class->id ? 'selected' : '' }}>
                                                {{ $class->name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Department Container --}}
                                <div class="col-md-6 mb-3" id="dept_container" style="{{ $package->class_department_id ? '' : 'display:none;' }}">
                                    <label class="fw-bold">Department</label>
                                    <select name="class_department_id" id="dept_id" class="form-control select2">
                                        <option value="">-- Select Department --</option>
                                        @if($package->class_department_id)
                                            <option value="{{ $package->class_department_id }}" selected>{{ $package->department->name_en }}</option>
                                        @endif
                                    </select>
                                </div>

                                {{-- Subjects (Multiple) --}}
                                <div class="col-md-12 mb-3">
                                    <label class="fw-bold">Subjects (Multiple Selection)</label>
                                    <select name="subject_ids[]" id="subject_ids" class="form-control select2" multiple="multiple">
                                        @foreach($package->subjects as $sub)
                                            <option value="{{ $sub->id }}" selected>{{ $sub->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Chapters (Multiple) --}}
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Chapters (Multiple Selection)</label>
                                    <select name="chapter_ids[]" id="chapter_ids" class="form-control select2" multiple="multiple">
                                        @foreach($package->chapters as $chap)
                                            <option value="{{ $chap->id }}" selected>{{ $chap->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Topics (Multiple) --}}
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Topics (Multiple Selection)</label>
                                    <select name="topic_ids[]" id="topic_ids" class="form-control select2" multiple="multiple">
                                        @foreach($package->topics as $top)
                                            <option value="{{ $top->id }}" selected>{{ $top->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Pricing & Validity --}}
                                <div class="col-md-4 mb-3">
                                    <label class="fw-bold">Exam Type</label>
                                    <select name="exam_type" id="exam_type" class="form-control">
                                        <option value="free" {{ $package->exam_type == 'free' ? 'selected' : '' }}>Free</option>
                                        <option value="paid" {{ $package->exam_type == 'paid' ? 'selected' : '' }}>Paid</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3" id="price_container" style="{{ $package->exam_type == 'paid' ? '' : 'display:none;' }}">
                                    <label class="fw-bold">Price (TK)</label>
                                    <input type="number" step="0.01" name="price" class="form-control" value="{{ $package->price }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="fw-bold">Validity (Days)</label>
                                    <input type="number" name="validity_days" class="form-control" value="{{ $package->validity_days }}" required>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="fw-bold">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="1" {{ $package->status == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ $package->status == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-info text-white px-5">Update Exam Package</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        // --- Class Change (Using Named Route) ---
        $('#class_id').change(function() {
            let classId = $(this).val();
            $('#dept_container').hide();
            $('#dept_id, #subject_ids, #chapter_ids, #topic_ids').empty();

            if(classId) {
                // Named Route logic
                let deptRoute = "{{ route('exam-package.get-departments', ':id') }}";
                deptRoute = deptRoute.replace(':id', classId);

                $.get(deptRoute, function(data) {
                    if(data.length > 0) {
                        $('#dept_container').show();
                        $('#dept_id').append('<option value="">-- Select --</option>');
                        data.forEach(d => $('#dept_id').append(`<option value="${d.id}">${d.name_en}</option>`));
                    } else {
                        loadSubjects(classId, null);
                    }
                });
            }
        });

        // --- Dept Change ---
        $('#dept_id').change(function() {
            loadSubjects($('#class_id').val(), $(this).val());
        });

        function loadSubjects(classId, deptId) {
            $.get("{{ route('exam-package.get-subjects') }}", {class_id: classId, department_id: deptId}, function(data) {
                $('#subject_ids, #chapter_ids, #topic_ids').empty();
                data.forEach(s => $('#subject_ids').append(`<option value="${s.id}">${s.name_en}</option>`));
            });
        }

        // --- Subjects to Chapters ---
        $('#subject_ids').change(function() {
            let ids = $(this).val();
            $.get("{{ route('exam-package.get-chapters') }}", {subject_ids: ids}, function(data) {
                $('#chapter_ids, #topic_ids').empty();
                data.forEach(c => $('#chapter_ids').append(`<option value="${c.id}">${c.name_en}</option>`));
            });
        });

        // --- Chapters to Topics ---
        $('#chapter_ids').change(function() {
            let ids = $(this).val();
            $.get("{{ route('exam-package.get-topics') }}", {chapter_ids: ids}, function(data) {
                $('#topic_ids').empty();
                data.forEach(t => $('#topic_ids').append(`<option value="${t.id}">${t.name_en}</option>`));
            });
        });

        // --- Type Toggle ---
        $('#exam_type').change(function() {
            $(this).val() === 'paid' ? $('#price_container').show() : $('#price_container').hide();
        });
    });
</script>
@endsection