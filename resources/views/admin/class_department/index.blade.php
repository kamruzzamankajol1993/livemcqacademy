@extends('admin.master.master')

@section('title') Class Department | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .sortable-list { list-style: none; padding: 0; }
    .sortable-list li { margin: 5px 0; padding: 10px; background: #fff; border: 1px solid #ddd; cursor: grab; display: flex; justify-content: space-between; align-items: center; border-radius: 4px; }
    .color-box { width: 30px; height: 30px; border-radius: 5px; border: 1px solid #ddd; display: inline-block; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Class Department List</h2>
            <button data-bs-toggle="modal" data-bs-target="#addModal" class="btn btn-primary text-white"><i class="fa fa-plus me-1"></i> Add New</button>
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
                    {{-- List View --}}
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
                                        <th>Name (EN)</th>
                                        <th>Color</th> {{-- New Column --}}
                                        <th>Assigned Classes</th>
                                        <th>Status</th>
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

                    {{-- Sort View --}}
                    <div class="tab-pane fade" id="sortView">
                        <ul id="sortable-list" class="sortable-list">
                            @foreach($departments as $item)
                            <li data-id="{{ $item->id }}">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fa fa-bars text-muted"></i>
                                    @if($item->icon) <img src="{{ asset($item->icon) }}" width="30"> @endif
                                    <strong>{{ $item->name_en }}</strong>
                                    @if($item->color) <span class="color-box" style="background: {{ $item->color }}; width: 15px; height: 15px;"></span> @endif
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

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('classDepartment.store') }}" method="POST" enctype="multipart/form-data" class="modal-content" id="addForm">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
                    <div class="col-md-12 mb-3">
                        <label>Assign Classes *</label>
                        <select name="class_ids[]" class="form-control select2-modal" multiple style="width:100%" required>
                            @foreach($classes as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Color</label>
                        <input type="color" name="color" class="form-control form-control-color">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Icon</label>
                        {{-- ID added for preview --}}
                        <input type="file" name="icon" id="add_icon_input" class="form-control" accept="image/*">
                        {{-- Preview Image Tag --}}
                        <img id="add_icon_preview" src="" width="60" class="mt-2 border rounded" style="display:none;">
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
            <div class="modal-header"><h5 class="modal-title">Edit Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
                    <div class="col-md-12 mb-3">
                        <label>Assign Classes *</label>
                        <select name="class_ids[]" id="edit_class_ids" class="form-control select2-modal" multiple style="width:100%" required>
                            @foreach($classes as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Color</label>
                        <input type="color" name="color" id="edit_color" class="form-control form-control-color">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Icon</label>
                        {{-- ID added for preview --}}
                        <input type="file" name="icon" id="edit_icon_input" class="form-control" accept="image/*">
                        {{-- Preview Image Tag --}}
                        <img id="edit_icon_preview" src="" width="60" class="mt-2 border rounded" style="display:none;">
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
        $('.select2-modal').select2({ dropdownParent: $('#addModal') });
        $('#editModal .select2-modal').select2({ dropdownParent: $('#editModal') });

        var routes = {
            fetch: "{{ route('ajax.classDepartment.data') }}",
            store: "{{ route('classDepartment.store') }}",
            show: "{{ route('classDepartment.show', ':id') }}",
            update: "{{ route('classDepartment.update', ':id') }}",
            destroy: "{{ route('classDepartment.destroy', ':id') }}",
            reorder: "{{ route('classDepartment.reorder') }}",
            csrf: "{{ csrf_token() }}"
        };

        // --- IMAGE PREVIEW LOGIC ---
        function readURL(input, previewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $(previewId).attr('src', e.target.result).show();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Add Modal Preview
        $('#add_icon_input').change(function() {
            readURL(this, '#add_icon_preview');
        });

        // Edit Modal Preview
        $('#edit_icon_input').change(function() {
            readURL(this, '#edit_icon_preview');
        });

        // --- DATA TABLE LOGIC ---
        var currentPage = 1, searchTerm = '';

        function fetchData() {
            $.get(routes.fetch, { page: currentPage, search: searchTerm }, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let sl = (res.current_page - 1) * res.per_page + i + 1;
                        let img = item.icon ? `<img src="{{ asset('') }}public/${item.icon}" width="40" class="rounded">` : '--';
                        let classes = item.classes.map(c => `<span class="badge bg-info text-dark">${c.name_en}</span>`).join(' ');
                        let status = item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        // Color Column Logic
                        let colorBox = item.color ? `<span class="color-box" style="background: ${item.color};"></span> <small class="text-muted">${item.color}</small>` : '--';
                        let destroyUrl = routes.destroy.replace(':id', item.id);

                        rows += `<tr>
                            <td>${sl}</td>
                            <td>${img}</td>
                            <td>${item.name_en} <br> <small class="text-muted">${item.name_bn}</small></td>
                            <td>${colorBox}</td>
                            <td>${classes}</td>
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
                    }
                }
                html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}"><a class="page-link" data-page="${data.current_page + 1}">Next</a></li>`;
            }
            $('#pagination').html(html);
        }

        fetchData();
        $(document).on('click', '.page-link', function(e) { e.preventDefault(); currentPage = $(this).data('page'); fetchData(); });
        $('#searchInput').on('keyup', function() { searchTerm = $(this).val(); currentPage = 1; fetchData(); });

        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var showUrl = routes.show.replace(':id', id);
            var updateUrl = routes.update.replace(':id', id);
            
            $.get(showUrl, function(data) {
                $('#edit_name_en').val(data.name_en);
                $('#edit_name_bn').val(data.name_bn);
                $('#edit_color').val(data.color);
                $('#edit_status').val(data.status);
                
                var selectedClasses = data.classes.map(function(c) { return c.id; });
                $('#edit_class_ids').val(selectedClasses).trigger('change');

                if(data.icon) {
                    $('#edit_icon_preview').attr('src', "{{ asset('') }}public/" + data.icon).show();
                } else {
                    $('#edit_icon_preview').hide();
                }

                $('#editForm').attr('action', updateUrl);
                $('#editModal').modal('show');
            });
        });

        // Reset forms on modal close
        $('#addModal, #editModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $(this).find('.select2-modal').val('').trigger('change');
            $(this).find('img').hide().attr('src', ''); // Clear preview
        });

        // Delete & Reorder (Standard)
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
                });
            }
        });
    });
</script>
@endsection