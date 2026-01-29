@extends('admin.master.master')

@section('title') Section Management | {{ $ins_name ?? 'App' }} @endsection

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
            <h2 class="mb-0">Section List</h2>
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
                                        <th class="sortable" data-column="name_en">Section Name</th>
                                        <th>Class</th>
                                        <th>Category</th>
                                        <th>Subject</th>
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
                            @foreach($sections as $item)
                            <li data-id="{{ $item->id }}">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fa fa-bars text-muted"></i>
                                    <strong>{{ $item->name_en }}</strong> ({{ $item->name_bn }})
                                </div>
                                <div class="d-flex gap-2">
                                    @if($item->class) <span class="badge bg-primary">{{ $item->class->name_en }}</span> @endif
                                    @if($item->subject) <span class="badge bg-info text-dark">{{ $item->subject->name_en }}</span> @endif
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

<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('section.import') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Import Sections</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <a href="{{ route('section.sample') }}" class="btn btn-info text-white btn-sm"><i class="fa fa-download me-1"></i> Download Sample</a>
                </div>
                <div class="mb-3">
                    <label>Upload Excel</label>
                    <input type="file" name="file" class="form-control" required accept=".xlsx,.xls,.csv">
                    <small class="text-muted">Columns: name_en, name_bn, class_name, subject_name, category_name</small>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-success">Import</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('section.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Section</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
                      <div class="col-md-4 mb-3">
                        <label>Category</label>
                        <select name="category_id" class="form-control select2-modal" style="width:100%">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Class *</label>
                        <select name="class_id" class="form-control select2-modal" style="width:100%" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $c) <option value="{{ $c->id }}">{{ $c->name_en }}</option> @endforeach
                        </select>
                    </div>
                  
                    <div class="col-md-4 mb-3">
                        <label>Subject</label>
                        <select name="subject_id" class="form-control select2-modal" style="width:100%">
                            <option value="">Select Subject</option>
                            @foreach($subjects as $sub) <option value="{{ $sub->id }}">{{ $sub->name_en }}</option> @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="editForm" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Edit Section</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
                     <div class="col-md-4 mb-3">
                        <label>Category</label>
                        <select name="category_id" id="edit_category_id" class="form-control select2-modal" style="width:100%">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Class *</label>
                        <select name="class_id" id="edit_class_id" class="form-control select2-modal" style="width:100%" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $c) <option value="{{ $c->id }}">{{ $c->name_en }}</option> @endforeach
                        </select>
                    </div>
                   
                    <div class="col-md-4 mb-3">
                        <label>Subject</label>
                        <select name="subject_id" id="edit_subject_id" class="form-control select2-modal" style="width:100%">
                            <option value="">Select Subject</option>
                            @foreach($subjects as $sub) <option value="{{ $sub->id }}">{{ $sub->name_en }}</option> @endforeach
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
        // --- 1. INITIALIZATION ---
        $('.select2-modal').select2({ dropdownParent: $('#addModal') });
        $('#editModal .select2-modal').select2({ dropdownParent: $('#editModal') });

        var routes = {
            fetch: "{{ route('ajax.section.data') }}",
            store: "{{ route('section.store') }}",
            show: "{{ route('section.show', ':id') }}",
            update: "{{ route('section.update', ':id') }}",
            destroy: "{{ route('section.destroy', ':id') }}",
            reorder: "{{ route('section.reorder') }}",
            getClasses: "{{ route('ajax.get.classes') }}",
            getSubjects: "{{ route('ajax.get.subjects') }}",
            csrf: "{{ csrf_token() }}"
        };

        // --- 2. DEPENDENCY LOGIC (ADD MODAL) ---
        
        // When Category changes in Add Modal -> Load Classes
        $('#addModal select[name="category_id"]').on('change', function() {
            var categoryId = $(this).val();
            var $classSelect = $('#addModal select[name="class_id"]');
            var $subjectSelect = $('#addModal select[name="subject_id"]');

            $classSelect.html('<option value="">Select Class</option>').trigger('change');
            $subjectSelect.html('<option value="">Select Subject</option>').trigger('change');

            if (categoryId) {
                $.get(routes.getClasses, { category_id: categoryId }, function(data) {
                    var options = '<option value="">Select Class</option>';
                    data.forEach(item => options += `<option value="${item.id}">${item.name_en}</option>`);
                    $classSelect.html(options).trigger('change');
                });
            }
        });

        // When Class changes in Add Modal -> Load Subjects
        $('#addModal select[name="class_id"]').on('change', function() {
            var classId = $(this).val();
            var $subjectSelect = $('#addModal select[name="subject_id"]');

            $subjectSelect.html('<option value="">Select Subject</option>').trigger('change');

            if (classId) {
                $.get(routes.getSubjects, { class_id: classId }, function(data) {
                    var options = '<option value="">Select Subject</option>';
                    data.forEach(item => options += `<option value="${item.id}">${item.name_en}</option>`);
                    $subjectSelect.html(options).trigger('change');
                });
            }
        });

        // --- 3. DEPENDENCY LOGIC (EDIT MODAL - USER INTERACTION) ---
        // These run if the user manually changes the dropdowns WHILE editing
        
        $('#edit_category_id').on('change', function(e) {
            if (!e.originalEvent) return; // Skip if triggered programmatically
            
            var categoryId = $(this).val();
            var $classSelect = $('#edit_class_id');
            var $subjectSelect = $('#edit_subject_id');

            $classSelect.html('<option value="">Select Class</option>').trigger('change');
            $subjectSelect.html('<option value="">Select Subject</option>').trigger('change');

            if (categoryId) {
                $.get(routes.getClasses, { category_id: categoryId }, function(data) {
                    var options = '<option value="">Select Class</option>';
                    data.forEach(item => options += `<option value="${item.id}">${item.name_en}</option>`);
                    $classSelect.html(options);
                });
            }
        });

        $('#edit_class_id').on('change', function(e) {
            if (!e.originalEvent) return; // Skip if triggered programmatically

            var classId = $(this).val();
            var $subjectSelect = $('#edit_subject_id');

            $subjectSelect.html('<option value="">Select Subject</option>').trigger('change');

            if (classId) {
                $.get(routes.getSubjects, { class_id: classId }, function(data) {
                    var options = '<option value="">Select Subject</option>';
                    data.forEach(item => options += `<option value="${item.id}">${item.name_en}</option>`);
                    $subjectSelect.html(options);
                });
            }
        });


        // --- 4. DATA TABLE & PAGINATION ---
        var currentPage = 1, searchTerm = '', sortColumn = 'serial', sortDirection = 'asc';

        function fetchData() {
            $.get(routes.fetch, { page: currentPage, search: searchTerm, sort: sortColumn, direction: sortDirection }, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let sl = (res.current_page - 1) * res.per_page + i + 1;
                        let className = item.class ? item.class.name_en : '--';
                        let catName = item.category ? item.category.name : '--';
                        let subName = item.subject ? item.subject.name_en : '--';
                        let status = item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        let destroyUrl = routes.destroy.replace(':id', item.id);

                        rows += `<tr>
                            <td>${sl}</td>
                            <td>${item.name_en} <br> <small class="text-muted">${item.name_bn}</small></td>
                            <td>${className}</td>
                            <td>${catName}</td>
                            <td>${subName}</td>
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
            // Using 'from' and 'to' directly from the controller response
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
        
        // Search & Pagination Events
        $('#searchInput').on('keyup', function() { searchTerm = $(this).val(); currentPage = 1; fetchData(); });
        $(document).on('click', '.page-link', function(e) { e.preventDefault(); let p = $(this).data('page'); if(p) { currentPage = p; fetchData(); } });

        // --- 5. EDIT BUTTON CLICK (PROGRAMMATIC LOADING) ---
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var showUrl = routes.show.replace(':id', id);
            var updateUrl = routes.update.replace(':id', id);
            
            $.get(showUrl, function(data) {
                // Basic Fields
                $('#edit_name_en').val(data.name_en);
                $('#edit_name_bn').val(data.name_bn);
                $('#edit_status').val(data.status);
                $('#editForm').attr('action', updateUrl);

                // --- CHAINED LOADING FOR DEPENDENCIES ---
                
                // 1. Set Category
                $('#edit_category_id').val(data.category_id).trigger('change.select2');

                // 2. Fetch Classes based on Category
                if(data.category_id) {
                    $.get(routes.getClasses, { category_id: data.category_id }, function(classes) {
                        var classOptions = '<option value="">Select Class</option>';
                        classes.forEach(c => classOptions += `<option value="${c.id}">${c.name_en}</option>`);
                        
                        $('#edit_class_id').html(classOptions);
                        
                        // 3. Set Class
                        $('#edit_class_id').val(data.class_id).trigger('change.select2');

                        // 4. Fetch Subjects based on Class
                        if(data.class_id) {
                            $.get(routes.getSubjects, { class_id: data.class_id }, function(subjects) {
                                var subOptions = '<option value="">Select Subject</option>';
                                subjects.forEach(s => subOptions += `<option value="${s.id}">${s.name_en}</option>`);
                                
                                $('#edit_subject_id').html(subOptions);
                                
                                // 5. Set Subject
                                $('#edit_subject_id').val(data.subject_id).trigger('change.select2');
                            });
                        }
                    });
                }
                
                $('#editModal').modal('show');
            });
        });

        // --- 6. DELETE & REORDER ---
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault(); const form = $(this).closest('form');
            Swal.fire({ title: 'Are you sure?', icon: 'warning', showCancelButton: true }).then((r) => { if(r.isConfirmed) form.submit(); });
        });

        $("#sortable-list").sortable({
            update: function(event, ui) {
                var order = [];
                $('#sortable-list li').each(function(index) { order.push({ id: $(this).data('id'), position: index + 1 }); });
                $.post(routes.reorder, { order: order, _token: routes.csrf }, function() {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Order Updated', showConfirmButton: false, timer: 1500 });
                    fetchData();
                });
            }
        });

        // Reset Modals on Close
        $('#addModal, #editModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $(this).find('.select2-modal').val('').trigger('change');
            
            // Reset dependent dropdowns specifically
            if($(this).attr('id') === 'addModal') {
                $(this).find('select[name="class_id"]').html('<option value="">Select Class</option>');
                $(this).find('select[name="subject_id"]').html('<option value="">Select Subject</option>');
            }
        });
    });
</script>
@endsection