@extends('admin.master.master')

@section('title') Academic Year Management | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<style>
    #sortable-list { list-style: none; padding: 0; }
    #sortable-list li { margin: 5px 0; padding: 10px; background: #fff; border: 1px solid #ddd; cursor: grab; display: flex; justify-content: space-between; align-items: center; border-radius: 4px; }
    #sortable-list li:hover { background: #f9f9f9; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Academic Year List</h2>
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
                                        <th class="sortable" data-column="name_en">Name (EN)</th>
                                        <th class="sortable" data-column="name_bn">Name (BN)</th>
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

                    {{-- Sort View --}}
                    <div class="tab-pane fade" id="sortView">
                        <div class="alert alert-info"><i class="fa fa-info-circle me-1"></i> Drag to reorder.</div>
                        <ul id="sortable-list">
                            @foreach($academicYears as $item)
                            <li data-id="{{ $item->id }}">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fa fa-bars text-muted"></i>
                                    <strong>{{ $item->name_en }}</strong> ({{ $item->name_bn }})
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
        <form action="{{ route('academicYear.import') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Import Academic Years</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <a href="{{ route('academicYear.sample') }}" class="btn btn-info text-white btn-sm"><i class="fa fa-download me-1"></i> Download Sample</a>
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
    <div class="modal-dialog">
        <form action="{{ route('academicYear.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Academic Year</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Name (English) *</label>
                    <input type="text" name="name_en" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Name (Bangla) *</label>
                    <input type="text" name="name_bn" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editForm" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Edit Academic Year</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Name (English) *</label>
                    <input type="text" name="name_en" id="edit_name_en" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Name (Bangla) *</label>
                    <input type="text" name="name_bn" id="edit_name_bn" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" id="edit_status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
    $(document).ready(function() {
        var routes = {
            fetch: "{{ route('ajax.academicYear.data') }}",
            store: "{{ route('academicYear.store') }}",
            show: "{{ route('academicYear.show', ':id') }}",
            update: "{{ route('academicYear.update', ':id') }}",
            destroy: "{{ route('academicYear.destroy', ':id') }}",
            reorder: "{{ route('academicYear.reorder') }}",
            csrf: "{{ csrf_token() }}"
        };

        var currentPage = 1, searchTerm = '', sortColumn = 'serial', sortDirection = 'asc';

        function fetchData() {
            $.get(routes.fetch, { page: currentPage, search: searchTerm, sort: sortColumn, direction: sortDirection }, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let sl = (res.current_page - 1) * res.per_page + i + 1;
                        let status = item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        let destroyUrl = routes.destroy.replace(':id', item.id);

                        rows += `<tr>
                            <td>${sl}</td>
                            <td>${item.name_en}</td>
                            <td>${item.name_bn}</td>
                            <td>${status}</td>
                            <td>
                                <button class="btn btn-sm btn-info btn-edit text-white" data-id="${item.id}"><i class="fa fa-edit"></i></button>
                                <form action="${destroyUrl}" method="POST" class="d-inline delete-form">@csrf @method('DELETE')<button class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i></button></form>
                            </td>
                        </tr>`;
                    });
                } else { rows = '<tr><td colspan="5" class="text-center text-muted">No records found</td></tr>'; }
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

        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var showUrl = routes.show.replace(':id', id);
            var updateUrl = routes.update.replace(':id', id);
            $.get(showUrl, function(data) {
                $('#edit_name_en').val(data.name_en);
                $('#edit_name_bn').val(data.name_bn);
                $('#edit_status').val(data.status);
                $('#editForm').attr('action', updateUrl);
                $('#editModal').modal('show');
            });
        });

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

        $('#addModal, #editModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
        });
    });
</script>
@endsection