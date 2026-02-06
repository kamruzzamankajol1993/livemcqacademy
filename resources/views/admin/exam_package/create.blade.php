@extends('admin.master.master')
@section('title', 'Create Exam Package')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('body')
<main class="main-content">

    @include('flash_message')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-11 mx-auto">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fa fa-plus-circle me-2"></i>Create New Exam Package</h5>
                        <a href="{{ route('exam-package.index') }}" class="btn btn-sm btn-light">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('exam-package.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                {{-- Exam Basic Info --}}
                                <div class="col-md-12 mb-3">
                                    <label class="fw-bold">Exam Name <span class="text-danger">*</span></label>
                                    <input type="text" name="exam_name" class="form-control" placeholder="Exam Name" required>
                                </div>

                                {{-- Class Selection --}}
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Class <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-control select2" required>
                                        <option value="">-- Select Class --</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Department Container --}}
                                <div class="col-md-6 mb-3" id="dept_container" style="display:none;">
                                    <label class="fw-bold">Department</label>
                                    <select name="class_department_id" id="dept_id" class="form-control select2">
                                        <option value="">-- Select Department --</option>
                                    </select>
                                </div>

                                {{-- Multiple Selections --}}
                                <div class="col-md-12 mb-3">
                                    <label class="fw-bold">Subjects (Multiple)</label>
                                    <select name="subject_ids[]" id="subject_ids" class="form-control select2" multiple="multiple">
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Chapters (Multiple)</label>
                                    <select name="chapter_ids[]" id="chapter_ids" class="form-control select2" multiple="multiple">
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Topics (Multiple)</label>
                                    <select name="topic_ids[]" id="topic_ids" class="form-control select2" multiple="multiple">
                                    </select>
                                </div>

                                {{-- Type & Pricing --}}
                                <div class="col-md-4 mb-3">
                                    <label class="fw-bold">Exam Type</label>
                                    <select name="exam_type" id="exam_type" class="form-control">
                                        <option value="free">Free</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3" id="price_container" style="display:none;">
                                    <label class="fw-bold">Price (TK)</label>
                                    <input type="number" name="price" class="form-control" value="0">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="fw-bold">Validity (Days)</label>
                                    <input type="number" name="validity_days" class="form-control" value="1" required>
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary px-5">Save Package</button>
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

        // --- Class to Department (Using Named Route) ---
        $('#class_id').change(function() {
            let classId = $(this).val();
            $('#dept_container').hide();
            $('#dept_id, #subject_ids, #chapter_ids, #topic_ids').empty();

            if(classId) {
                // url() এর পরিবর্তে route() ব্যবহার করা হয়েছে
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

        $('#dept_id').change(function() {
            loadSubjects($('#class_id').val(), $(this).val());
        });

        function loadSubjects(classId, deptId) {
            $.get("{{ route('exam-package.get-subjects') }}", {class_id: classId, department_id: deptId}, function(data) {
                $('#subject_ids').empty();
                data.forEach(s => $('#subject_ids').append(`<option value="${s.id}">${s.name_en}</option>`));
            });
        }

        $('#subject_ids').change(function() {
            $.get("{{ route('exam-package.get-chapters') }}", {subject_ids: $(this).val()}, function(data) {
                $('#chapter_ids').empty();
                data.forEach(c => $('#chapter_ids').append(`<option value="${c.id}">${c.name_en}</option>`));
            });
        });

        $('#chapter_ids').change(function() {
            $.get("{{ route('exam-package.get-topics') }}", {chapter_ids: $(this).val()}, function(data) {
                $('#topic_ids').empty();
                data.forEach(t => $('#topic_ids').append(`<option value="${t.id}">${t.name_en}</option>`));
            });
        });

        $('#exam_type').change(function() {
            $(this).val() === 'paid' ? $('#price_container').show() : $('#price_container').hide();
        });
    });
</script>
@endsection