@extends('admin.master.master')

@section('title') Subject Management | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
    .color-preview-box { width: 100%; height: 38px; border: 1px solid #ced4da; border-radius: 4px; background: #eee; }
    .table-color-preview { width: 25px; height: 25px; border-radius: 50%; border: 1px solid #ccc; display: inline-block; vertical-align: middle; }
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
            <h2 class="mb-0">Subject List</h2>
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
                                        <th>Icon</th>
                                        <th class="sortable" data-column="name_en">Name</th>
                                        <th>Parent</th>
                                        <th>Classes</th>
                                        <th>Departments</th>
                                        <th>Color</th>
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
                            @foreach($subjects as $item)
                            <li data-id="{{ $item->id }}">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fa fa-bars text-muted"></i>
                                    @if($item->icon) <img src="{{ asset('public/'.$item->icon) }}" width="30"> @endif
                                    <strong>{{ $item->name_en }}</strong> ({{ $item->name_bn }})
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    @if($item->color) <div class="table-color-preview" style="background: {{ $item->color }}"></div> @endif
                                    <span class="badge bg-secondary">{{ $item->classes->count() }} Classes</span>
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
        <form action="{{ route('subject.import') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Import Subjects</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <a href="{{ route('subject.sample') }}" class="btn btn-info text-white btn-sm"><i class="fa fa-download me-1"></i> Download Sample</a>
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
        <form action="{{ route('subject.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Subject</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row">
                    {{-- Name Fields --}}
                    <div class="col-md-6 mb-3">
                        <label>Name (English) *</label>
                        <input type="text" name="name_en" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Name (Bangla) *</label>
                        <input type="text" name="name_bn" class="form-control" required>
                    </div>

                    {{-- Parent & Class --}}
                    <div class="col-md-6 mb-3">
                        <label>Parent Subject</label>
                        <select name="parent_id" class="form-control select2-modal" style="width:100%">
                            <option value="">None</option>
                            @foreach($parents as $p) <option value="{{ $p->id }}">{{ $p->name_en }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Classes *</label>
                        {{-- ID added for AJAX --}}
                        <select name="class_ids[]" id="add_class_ids" class="form-control select2-modal" multiple required style="width:100%">
                            @foreach($classes as $c) <option value="{{ $c->id }}">{{ $c->name_en }}</option> @endforeach
                        </select>
                    </div>

                    {{-- NEW DEPARTMENT DROPDOWN --}}
                    <div class="col-md-12 mb-3">
                        <label>Departments (Auto-loaded based on Class)</label>
                        <select name="department_ids[]" id="add_department_ids" class="form-control select2-modal" multiple style="width:100%">
                            <option value="">Select Classes First</option>
                        </select>
                    </div>

                    {{-- Color & Icon --}}
                    <div class="col-md-6 mb-3">
                        <label>Color</label>
                        <div class="input-group">
                            <input type="text" name="color" id="add_color" class="form-control">
                            <input type="color" class="form-control" style="max-width:50px" onchange="updateColor(this, 'add_color', 'add_prev')">
                        </div>
                        <div id="add_prev" class="color-preview-box"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Icon</label>
                        <input type="file" name="icon" class="form-control" onchange="previewImg(this, 'add_img_prev')">
                        <img id="add_img_prev" src="#" style="display:none; width:50px; margin-top:5px" class="img-thumbnail">
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
        <form id="editForm" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Edit Subject</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row">
                    {{-- Names --}}
                    <div class="col-md-6 mb-3">
                        <label>Name (English) *</label>
                        <input type="text" name="name_en" id="edit_name_en" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Name (Bangla) *</label>
                        <input type="text" name="name_bn" id="edit_name_bn" class="form-control" required>
                    </div>

                    {{-- Parent & Class --}}
                    <div class="col-md-6 mb-3">
                        <label>Parent Subject</label>
                        <select name="parent_id" id="edit_parent_id" class="form-control select2-modal" style="width:100%">
                            <option value="">None</option>
                            @foreach($parents as $p) <option value="{{ $p->id }}">{{ $p->name_en }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Classes *</label>
                        <select name="class_ids[]" id="edit_class_ids" class="form-control select2-modal" multiple required style="width:100%">
                            @foreach($classes as $c) <option value="{{ $c->id }}">{{ $c->name_en }}</option> @endforeach
                        </select>
                    </div>

                    {{-- NEW DEPARTMENT DROPDOWN --}}
                    <div class="col-md-12 mb-3">
                        <label>Departments</label>
                        <select name="department_ids[]" id="edit_department_ids" class="form-control select2-modal" multiple style="width:100%">
                            {{-- Options will be loaded via AJAX --}}
                        </select>
                    </div>

                    {{-- Color & Icon & Status --}}
                    <div class="col-md-6 mb-3">
                        <label>Color</label>
                        <div class="input-group">
                            <input type="text" name="color" id="edit_color" class="form-control">
                            <input type="color" class="form-control" style="max-width:50px" onchange="updateColor(this, 'edit_color', 'edit_prev')">
                        </div>
                        <div id="edit_prev" class="color-preview-box"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Icon</label>
                        <input type="file" name="icon" class="form-control" onchange="previewImg(this, 'edit_img_prev')">
                        <img id="edit_img_prev" src="#" style="display:none; width:50px; margin-top:5px" class="img-thumbnail">
                    </div>
                    <div class="col-md-6 mb-3">
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
    // Helper: Image Preview
    function previewImg(input, id) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#' + id).attr('src', e.target.result).show();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Helper: Color Update
    function updateColor(picker, inputId, boxId) {
        $('#' + inputId).val(picker.value);
        $('#' + boxId).css('background', picker.value);
    }

    $(document).ready(function() {
        // Initialize Select2
        $('.select2-modal').select2({ dropdownParent: $('#addModal') });
        $('#editModal .select2-modal').select2({ dropdownParent: $('#editModal') });

        var routes = {
            fetch: "{{ route('ajax.subject.data') }}",
            store: "{{ route('subject.store') }}",
            show: "{{ route('subject.show', ':id') }}",
            update: "{{ route('subject.update', ':id') }}",
            destroy: "{{ route('subject.destroy', ':id') }}",
            reorder: "{{ route('subject.reorder') }}",
            getDepartments: "{{ route('ajax.get.departments') }}", // Route for fetching departments
            csrf: "{{ csrf_token() }}"
        };

        // --- 1. ADD MODAL: DEPENDENCY LOGIC ---
        
        // When Classes change -> Fetch Departments
        $('#add_class_ids').on('change', function() {
            var classIds = $(this).val();
            var $deptSelect = $('#add_department_ids');

            // Reset department dropdown
            $deptSelect.html('').trigger('change');

            if (classIds.length > 0) {
                $.get(routes.getDepartments, { class_ids: classIds }, function(data) {
                    var options = '';
                    data.forEach(function(d) {
                        options += `<option value="${d.id}">${d.name_en}</option>`;
                    });
                    $deptSelect.html(options).trigger('change');
                });
            }
        });

        // --- 2. EDIT MODAL: DEPENDENCY LOGIC (User Interaction) ---
        
        $('#edit_class_ids').on('change', function(e) {
            // Only run if triggered by user (to avoid conflict with Edit Button logic)
            if (e.originalEvent) {
                var classIds = $(this).val();
                var $deptSelect = $('#edit_department_ids');
                
                $deptSelect.html('').trigger('change');

                if (classIds.length > 0) {
                    $.get(routes.getDepartments, { class_ids: classIds }, function(data) {
                        var options = '';
                        data.forEach(function(d) {
                            options += `<option value="${d.id}">${d.name_en}</option>`;
                        });
                        $deptSelect.html(options).trigger('change');
                    });
                }
            }
        });

        // --- 3. DATA TABLE & PAGINATION ---
        var currentPage = 1, searchTerm = '', sortColumn = 'serial', sortDirection = 'asc';

        function fetchData() {
            $.get(routes.fetch, { page: currentPage, search: searchTerm, sort: sortColumn, direction: sortDirection }, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let serial = (res.current_page - 1) * res.per_page + i + 1;
                        let icon = item.icon ? `{{ asset('') }}public/${item.icon}` : 'https://placehold.co/40x40/EFEFEF/AAAAAA&text=No+Icon';
                        let color = item.color ? `<div class="table-color-preview" style="background:${item.color}"></div>` : '--';
                        let parent = item.parent ? item.parent.name_en : '<span class="text-muted">--</span>';
                        
                        // Badges for Classes
                        let classes = item.classes.map(c => `<span class="badge bg-info text-dark">${c.name_en}</span>`).join(' ');
                        
                        // Badges for Departments
                        let depts = item.departments && item.departments.length > 0 
                                    ? item.departments.map(d => `<span class="badge bg-warning text-dark">${d.name_en}</span>`).join(' ') 
                                    : '<small class="text-muted">--</small>';

                        let status = item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        let destroyUrl = routes.destroy.replace(':id', item.id);

                        rows += `<tr>
                            <td>${serial}</td>
                            <td><img src="${icon}" width="30" class="img-thumbnail"></td>
                            <td>${item.name_en}<br><small class="text-muted">${item.name_bn}</small></td>
                            <td>${parent}</td>
                            <td>${classes}</td>
                            <td>${depts}</td>
                            <td>${color}</td>
                            <td>${status}</td>
                            <td>
                                <button class="btn btn-sm btn-info btn-edit text-white" data-id="${item.id}"><i class="fa fa-edit"></i></button>
                                <form action="${destroyUrl}" method="POST" class="d-inline delete-form">@csrf @method('DELETE')<button class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i></button></form>
                            </td>
                        </tr>`;
                    });
                } else { rows = '<tr><td colspan="9" class="text-center text-muted">No records found</td></tr>'; }
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
                    }
                }
                html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}"><a class="page-link" data-page="${data.current_page + 1}">Next</a></li>`;
            }
            $('#pagination').html(html);
        }

        fetchData();
        $(document).on('click', '.page-link', function(e) { e.preventDefault(); currentPage = $(this).data('page'); fetchData(); });
        $('#searchInput').on('keyup', function() { searchTerm = $(this).val(); currentPage = 1; fetchData(); });

        // --- 4. EDIT BUTTON CLICK ---
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var showUrl = routes.show.replace(':id', id);
            var updateUrl = routes.update.replace(':id', id);
            
            $.get(showUrl, function(data) {
                // Fill Basic Fields
                $('#edit_name_en').val(data.name_en);
                $('#edit_name_bn').val(data.name_bn);
                $('#edit_parent_id').val(data.parent_id).trigger('change');
                $('#edit_color').val(data.color);
                $('#edit_prev').css('background', data.color);
                $('#edit_status').val(data.status);
                
                // --- Complex Logic for Classes & Departments ---
                
                // 1. Get Saved Class IDs
                var classIds = data.classes.map(c => c.id);
                // 2. Set them in Dropdown (Trigger change but handle logic manually below to ensure order)
                $('#edit_class_ids').val(classIds).trigger('change.select2');

                // 3. Fetch Departments based on these Classes
                if(classIds.length > 0) {
                     $.get(routes.getDepartments, { class_ids: classIds }, function(deptData) {
                        var options = '';
                        // 4. Get Saved Department IDs
                        var savedDeptIds = data.departments ? data.departments.map(d => d.id) : [];
                        
                        // 5. Build Options
                        deptData.forEach(function(d) {
                            options += `<option value="${d.id}">${d.name_en}</option>`;
                        });
                        
                        $('#edit_department_ids').html(options);
                        
                        // 6. Select Saved Departments
                        $('#edit_department_ids').val(savedDeptIds).trigger('change');
                    });
                } else {
                    $('#edit_department_ids').html('');
                }

                // Image Preview
                if(data.icon) $('#edit_img_prev').attr('src', `{{ asset('') }}public/${data.icon}`).show();
                else $('#edit_img_prev').hide();

                $('#editForm').attr('action', updateUrl);
                $('#editModal').modal('show');
            });
        });

        // --- 5. DELETE & REORDER ---
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

        // Reset Modal State
        $('#addModal, #editModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $(this).find('.select2-modal').val('').trigger('change');
            $('#add_img_prev, #edit_img_prev').hide().attr('src', '#');
            $('#add_prev, #edit_prev').css('background', '');
            
            // Clear department dropdowns
            if($(this).attr('id') === 'addModal') {
                $('#add_department_ids').html('<option value="">Select Classes First</option>');
            }
        });
    });
</script>
@endsection