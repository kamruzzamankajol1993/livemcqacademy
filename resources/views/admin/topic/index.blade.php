@extends('admin.master.master')

@section('title') Topic Management | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
    #sortable-list { list-style: none; padding: 0; }
    #sortable-list li { margin: 5px 0; padding: 10px; background: #fff; border: 1px solid #ddd; cursor: grab; display: flex; justify-content: space-between; align-items: center; border-radius: 4px; }
    #sortable-list li:hover { background: #f9f9f9; }
    .select2-container { z-index: 9999; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Topic List</h2>
            <div class="d-flex gap-2">
                <button data-bs-toggle="modal" data-bs-target="#importModal" class="btn btn-success text-white"><i class="fa fa-file-excel me-1"></i> Import</button>
                <button data-bs-toggle="modal" data-bs-target="#addModal" class="btn btn-primary text-white"><i class="fa fa-plus me-1"></i> Add New</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#listView">List View</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sortView">Drag & Drop Sort</button></li>
                </ul>
            </div>
            <div class="card-body">
                @include('flash_message')
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="listView">
                        <div class="d-flex justify-content-end mb-3">
                            <input class="form-control" id="searchInput" type="search" placeholder="Search..." style="max-width: 250px;">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Sl</th>
                                        <th class="sortable" data-column="name_en">Topic Name</th>
                                        <th>Class</th>
                                        <th>Subject</th>
                                        <th>Chapter</th>
                                        <th class="sortable" data-column="status">Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody"></tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted" id="pagination-info"></div>
                            <nav><ul class="pagination justify-content-center mb-0" id="pagination"></ul></nav>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="sortView">
                        <div class="alert alert-info"><i class="fa fa-info-circle me-1"></i> Drag to reorder.</div>
                        <ul id="sortable-list">
                            @foreach($topics as $item)
                            <li data-id="{{ $item->id }}">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fa fa-bars text-muted"></i>
                                    <strong>{{ $item->name_en }}</strong>
                                </div>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-secondary">{{ $item->class->name_en ?? '--' }}</span>
                                    <span class="badge bg-primary">{{ $item->subject->name_en ?? '--' }}</span>
                                    <span class="badge bg-info text-dark">{{ $item->chapter->name_en ?? '--' }}</span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('topic.import') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Import Topics</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <a href="{{ route('topic.sample') }}" class="btn btn-info text-white btn-sm"><i class="fa fa-download me-1"></i> Download Sample</a>
                </div>
                <div class="mb-3">
                    <label>Upload Excel</label>
                    <input type="file" name="file" class="form-control" required accept=".xlsx,.xls,.csv">
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-success">Import</button></div>
        </form>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('topic.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Topic</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Name (English) *</label>
                        <input type="text" name="name_en" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Name (Bangla) *</label>
                        <input type="text" name="name_bn" class="form-control" required>
                    </div>

                    {{-- 1. Class --}}
                    <div class="col-md-4 mb-3">
                        <label>Class *</label>
                        <select name="class_id" class="form-control select2-modal" style="width:100%" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $cls) 
                                <option value="{{ $cls->id }}">{{ $cls->name_en }}</option> 
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. Subject --}}
                    <div class="col-md-4 mb-3">
                        <label>Subject *</label>
                        <select name="subject_id" class="form-control select2-modal" style="width:100%" required>
                            <option value="">Select Subject</option>
                        </select>
                    </div>

                    {{-- 3. Chapter --}}
                    <div class="col-md-4 mb-3">
                        <label>Chapter *</label>
                        <select name="chapter_id" class="form-control select2-modal" style="width:100%" required>
                            <option value="">Select Chapter</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="editForm" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Edit Topic</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Name (English) *</label>
                        <input type="text" name="name_en" id="edit_name_en" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Name (Bangla) *</label>
                        <input type="text" name="name_bn" id="edit_name_bn" class="form-control" required>
                    </div>

                    {{-- Edit Class --}}
                    <div class="col-md-4 mb-3">
                        <label>Class *</label>
                        <select name="class_id" id="edit_class_id" class="form-control select2-modal" style="width:100%" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $cls) 
                                <option value="{{ $cls->id }}">{{ $cls->name_en }}</option> 
                            @endforeach
                        </select>
                    </div>

                    {{-- Edit Subject --}}
                    <div class="col-md-4 mb-3">
                        <label>Subject *</label>
                        <select name="subject_id" id="edit_subject_id" class="form-control select2-modal" style="width:100%" required>
                            <option value="">Select Subject</option>
                        </select>
                    </div>

                    {{-- Edit Chapter --}}
                    <div class="col-md-4 mb-3">
                        <label>Chapter *</label>
                        <select name="chapter_id" id="edit_chapter_id" class="form-control select2-modal" style="width:100%" required>
                            <option value="">Select Chapter</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Status</label>
                        <select name="status" id="edit_status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
    $(document).ready(function() {
        // Init Select2
        $('.select2-modal').select2({ dropdownParent: $('#addModal') });
        $('#editModal .select2-modal').select2({ dropdownParent: $('#editModal') });

        var routes = {
            fetch: "{{ route('ajax.topic.data') }}",
            store: "{{ route('topic.store') }}",
            show: "{{ route('topic.show', ':id') }}",
            update: "{{ route('topic.update', ':id') }}",
            destroy: "{{ route('topic.destroy', ':id') }}",
            reorder: "{{ route('topic.reorder') }}",
            // Helper Routes
            getSubjects: "{{ route('ajax.get.subjects') }}",
            getChapters: "{{ route('ajax.get.chapters') }}",
            csrf: "{{ csrf_token() }}"
        };

        // --- ADD MODAL DEPENDENCY ---
        // 1. Class -> Load Subjects
        $('#addModal select[name="class_id"]').on('change', function() {
            var classId = $(this).val();
            var $subjectSelect = $('#addModal select[name="subject_id"]');
            var $chapterSelect = $('#addModal select[name="chapter_id"]');

            $subjectSelect.html('<option value="">Select Subject</option>').trigger('change');
            $chapterSelect.html('<option value="">Select Chapter</option>').trigger('change');

            if (classId) {
                $.get(routes.getSubjects, { class_id: classId }, function(data) {
                    var options = '<option value="">Select Subject</option>';
                    data.forEach(item => options += `<option value="${item.id}">${item.name_en}</option>`);
                    $subjectSelect.html(options).trigger('change');
                });
            }
        });

        // 2. Subject -> Load Chapters (Using Class ID + Subject ID)
        $('#addModal select[name="subject_id"]').on('change', function() {
            var subjectId = $(this).val();
            var classId = $('#addModal select[name="class_id"]').val();
            var $chapterSelect = $('#addModal select[name="chapter_id"]');

            $chapterSelect.html('<option value="">Select Chapter</option>').trigger('change');

            if (classId && subjectId) {
                $.get(routes.getChapters, { class_id: classId, subject_id: subjectId }, function(data) {
                    var options = '<option value="">Select Chapter</option>';
                    data.forEach(item => options += `<option value="${item.id}">${item.name_en}</option>`);
                    $chapterSelect.html(options).trigger('change');
                });
            }
        });

        // --- EDIT MODAL DEPENDENCY (User Interaction) ---
        $('#edit_class_id').on('change', function(e) {
            if (!e.originalEvent) return; // Ignore programmatic changes
            var classId = $(this).val();
            var $subjectSelect = $('#edit_subject_id');
            var $chapterSelect = $('#edit_chapter_id');

            $subjectSelect.html('<option value="">Select Subject</option>').trigger('change');
            $chapterSelect.html('<option value="">Select Chapter</option>').trigger('change');

            if (classId) {
                $.get(routes.getSubjects, { class_id: classId }, function(data) {
                    var options = '<option value="">Select Subject</option>';
                    data.forEach(item => options += `<option value="${item.id}">${item.name_en}</option>`);
                    $subjectSelect.html(options);
                });
            }
        });

        $('#edit_subject_id').on('change', function(e) {
            if (!e.originalEvent) return; 
            var subjectId = $(this).val();
            var classId = $('#edit_class_id').val();
            var $chapterSelect = $('#edit_chapter_id');

            $chapterSelect.html('<option value="">Select Chapter</option>').trigger('change');

            if (classId && subjectId) {
                $.get(routes.getChapters, { class_id: classId, subject_id: subjectId }, function(data) {
                    var options = '<option value="">Select Chapter</option>';
                    data.forEach(item => options += `<option value="${item.id}">${item.name_en}</option>`);
                    $chapterSelect.html(options);
                });
            }
        });

        // --- CRUD Operations ---
        var currentPage = 1, searchTerm = '', sortColumn = 'serial', sortDirection = 'asc';

        function fetchData() {
            $.get(routes.fetch, { page: currentPage, search: searchTerm, sort: sortColumn, direction: sortDirection }, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let sl = (res.current_page - 1) * res.per_page + i + 1;
                        let clsName = item.class ? item.class.name_en : '--';
                        let subName = item.subject ? item.subject.name_en : '--';
                        let chapName = item.chapter ? item.chapter.name_en : '--';
                        let status = item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        let destroyUrl = routes.destroy.replace(':id', item.id);

                        rows += `<tr>
                            <td>${sl}</td>
                            <td>${item.name_en} <br> <small class="text-muted">${item.name_bn}</small></td>
                            <td>${clsName}</td>
                            <td>${subName}</td>
                            <td>${chapName}</td>
                            <td>${status}</td>
                            <td>
                                <button class="btn btn-sm btn-info btn-edit text-white" data-id="${item.id}"><i class="fa fa-edit"></i></button>
                                <form action="${destroyUrl}" method="POST" class="d-inline delete-form">@csrf @method('DELETE')<button class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i></button></form>
                            </td>
                        </tr>`;
                    });
                } else { rows = '<tr><td colspan="7" class="text-center text-muted">No records found</td></tr>'; }
                $('#tableBody').html(rows);
                renderPagination(res);
            });
        }

        function renderPagination(data) {
            let html = '';
            if(data.total > 0) $('#pagination-info').html(`Showing ${data.from} to ${data.to} of ${data.total} entries`);
            else $('#pagination-info').html('');

            if(data.last_page > 1) {
                html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}"><a class="page-link" data-page="${data.current_page - 1}">Prev</a></li>`;
                for(let i=1; i<=data.last_page; i++) {
                    if(i==1 || i==data.last_page || (i>=data.current_page-1 && i<=data.current_page+1)) {
                        html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" data-page="${i}">${i}</a></li>`;
                    } else if(i==data.current_page-2 || i==data.current_page+2) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                }
                html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}"><a class="page-link" data-page="${data.current_page + 1}">Next</a></li>`;
            }
            $('#pagination').html(html);
        }

        fetchData();
        $('#searchInput').on('keyup', function() { searchTerm = $(this).val(); currentPage = 1; fetchData(); });
        $(document).on('click', '.page-link', function(e) { e.preventDefault(); let p = $(this).data('page'); if(p) { currentPage = p; fetchData(); } });

        // --- EDIT CLICK LOGIC (Chained Loading) ---
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var showUrl = routes.show.replace(':id', id);
            var updateUrl = routes.update.replace(':id', id);
            
            $.get(showUrl, function(data) {
                $('#edit_name_en').val(data.name_en);
                $('#edit_name_bn').val(data.name_bn);
                $('#edit_status').val(data.status);
                $('#editForm').attr('action', updateUrl);

                // 1. Set Class
                $('#edit_class_id').val(data.class_id).trigger('change.select2');

                // 2. Fetch Subjects based on Class
                if(data.class_id) {
                    $.get(routes.getSubjects, { class_id: data.class_id }, function(subjects) {
                        var subOptions = '<option value="">Select Subject</option>';
                        subjects.forEach(s => subOptions += `<option value="${s.id}">${s.name_en}</option>`);
                        $('#edit_subject_id').html(subOptions);
                        
                        // 3. Set Subject
                        $('#edit_subject_id').val(data.subject_id).trigger('change.select2');

                        // 4. Fetch Chapters based on Class & Subject
                        if(data.subject_id) {
                            $.get(routes.getChapters, { class_id: data.class_id, subject_id: data.subject_id }, function(chapters) {
                                var chapOptions = '<option value="">Select Chapter</option>';
                                chapters.forEach(c => chapOptions += `<option value="${c.id}">${c.name_en}</option>`);
                                $('#edit_chapter_id').html(chapOptions);
                                
                                // 5. Set Chapter
                                $('#edit_chapter_id').val(data.chapter_id).trigger('change.select2');
                            });
                        }
                    });
                }
                $('#editModal').modal('show');
            });
        });

        $(document).on('click', '.btn-delete', function(e) { e.preventDefault(); const form = $(this).closest('form'); Swal.fire({ title: 'Are you sure?', icon: 'warning', showCancelButton: true }).then((r) => { if(r.isConfirmed) form.submit(); }); });
        
        $("#sortable-list").sortable({
            update: function(event, ui) {
                var order = [];
                $('#sortable-list li').each(function(index) { order.push({ id: $(this).data('id'), position: index + 1 }); });
                $.post(routes.reorder, { order: order, _token: routes.csrf }, function() { Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Order Updated', showConfirmButton: false, timer: 1500 }); fetchData(); });
            }
        });

        $('#addModal, #editModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $(this).find('.select2-modal').val('').trigger('change');
            if($(this).attr('id') === 'addModal') {
                $(this).find('select[name="subject_id"]').html('<option value="">Select Subject</option>');
                $(this).find('select[name="chapter_id"]').html('<option value="">Select Chapter</option>');
            }
        });
    });
</script>
@endsection