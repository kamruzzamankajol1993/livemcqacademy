@extends('admin.master.master')

@section('title') Institute Management | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<style>
    .sortable-list { list-style: none; padding: 0; }
    .sortable-list li { margin: 5px 0; padding: 10px; background: #fff; border: 1px solid #ddd; cursor: grab; display: flex; justify-content: space-between; align-items: center; border-radius: 4px; }
    .sortable-list li:hover { background: #f9f9f9; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Institute List</h2>
            <div class="d-flex gap-2">
                <button data-bs-toggle="modal" data-bs-target="#importModal" class="btn btn-success text-white"><i class="fa fa-file-excel me-1"></i> Import</button>
                <button data-bs-toggle="modal" data-bs-target="#addModal" class="btn btn-primary text-white"><i class="fa fa-plus me-1"></i> Add New</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs" id="instituteTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#schoolTab" data-type="school">School</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#collegeTab" data-type="college">College</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#universityTab" data-type="university">University</button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
                @include('flash_message')
                
                {{-- Global Search for Current Tab --}}
                <div class="d-flex justify-content-end mb-3">
                    <input class="form-control" id="searchInput" type="search" placeholder="Search..." style="max-width: 250px;">
                </div>

                <div class="tab-content" id="instituteTabContent">
                    {{-- School Tab --}}
                    <div class="tab-pane fade show active" id="schoolTab">
                        @include('admin.institute.tab_content', ['type' => 'school', 'items' => $schools])
                    </div>
                    {{-- College Tab --}}
                    <div class="tab-pane fade" id="collegeTab">
                        @include('admin.institute.tab_content', ['type' => 'college', 'items' => $colleges])
                    </div>
                    {{-- University Tab --}}
                    <div class="tab-pane fade" id="universityTab">
                        @include('admin.institute.tab_content', ['type' => 'university', 'items' => $universities])
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- Reusable Tab Content --}}
{{-- Note: This is inline for simplicity, you can make a partial file --}}
<script type="text/template" id="tab-content-template">
    </script>

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('institute.import') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Import Institutes</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <a href="{{ route('institute.sample') }}" class="btn btn-info text-white btn-sm"><i class="fa fa-download me-1"></i> Download Sample</a>
                </div>
                <div class="mb-3">
                    <label>Upload Excel</label>
                    <input type="file" name="file" class="form-control" required accept=".xlsx,.xls,.csv">
                    <small class="text-muted">Column 'type' must be: school, college, or university</small>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-success">Import</button></div>
        </form>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('institute.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Institute</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Type *</label>
                    <select name="type" class="form-control" required>
                        <option value="school">School</option>
                        <option value="college">College</option>
                        <option value="university">University</option>
                    </select>
                </div>
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
            <div class="modal-header"><h5 class="modal-title">Edit Institute</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Type *</label>
                    <select name="type" id="edit_type" class="form-control" required>
                        <option value="school">School</option>
                        <option value="college">College</option>
                        <option value="university">University</option>
                    </select>
                </div>
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
            fetch: "{{ route('ajax.institute.data') }}",
            store: "{{ route('institute.store') }}",
            show: "{{ route('institute.show', ':id') }}",
            update: "{{ route('institute.update', ':id') }}",
            destroy: "{{ route('institute.destroy', ':id') }}",
            reorder: "{{ route('institute.reorder') }}",
            csrf: "{{ csrf_token() }}"
        };

        var activeType = 'school'; // Default
        var currentPage = 1, searchTerm = '';

        // Tab Change Event
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            activeType = $(e.target).data('type');
            currentPage = 1;
            fetchData();
        });

        // Search
        $('#searchInput').on('keyup', function() {
            searchTerm = $(this).val();
            currentPage = 1;
            fetchData();
        });

        function fetchData() {
            // Find current active table body
            let tableBodyId = '#tableBody_' + activeType;
            let paginationId = '#pagination_' + activeType;
            let infoId = '#info_' + activeType;

            $.get(routes.fetch, { page: currentPage, search: searchTerm, type: activeType }, function(res) {
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
                
                $(tableBodyId).html(rows);
                renderPagination(res, paginationId, infoId);
            });
        }

        function renderPagination(data, paginationSelector, infoSelector) {
            let html = '';
            if(data.total > 0) $(infoSelector).html(`Showing ${data.from} to ${data.to} of ${data.total} entries`);
            else $(infoSelector).html('');

            if(data.last_page > 1) {
                html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}"><a class="page-link" data-page="${data.current_page - 1}">Prev</a></li>`;
                for(let i=1; i<=data.last_page; i++) {
                    if(i==1 || i==data.last_page || (i>=data.current_page-1 && i<=data.current_page+1)) {
                        html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" data-page="${i}">${i}</a></li>`;
                    }
                }
                html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}"><a class="page-link" data-page="${data.current_page + 1}">Next</a></li>`;
            }
            $(paginationSelector).html(html);
        }

        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            let p = $(this).data('page');
            if(p) { currentPage = p; fetchData(); }
        });

        // Initialize First Data Load
        fetchData();

        // Edit
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var showUrl = routes.show.replace(':id', id);
            var updateUrl = routes.update.replace(':id', id);
            $.get(showUrl, function(data) {
                $('#edit_name_en').val(data.name_en);
                $('#edit_name_bn').val(data.name_bn);
                $('#edit_type').val(data.type);
                $('#edit_status').val(data.status);
                $('#editForm').attr('action', updateUrl);
                $('#editModal').modal('show');
            });
        });

        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault(); const form = $(this).closest('form');
            Swal.fire({ title: 'Are you sure?', icon: 'warning', showCancelButton: true }).then((r) => { if(r.isConfirmed) form.submit(); });
        });

        // Sortable for all lists (Separate IDs)
        $(".sortable-list").sortable({
            update: function(event, ui) {
                var order = [];
                // Find which list is currently being sorted
                var listId = $(this).attr('id'); 
                
                $('#' + listId + ' li').each(function(index) {
                    order.push({ id: $(this).data('id'), position: index + 1 });
                });

                $.post(routes.reorder, { order: order, _token: routes.csrf }, function() {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Order Updated', showConfirmButton: false, timer: 1500 });
                    // No need to fetch data here to prevent reload jump, UI is already updated by Sortable
                });
            }
        });
        
        $('#addModal, #editModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
        });
    });
</script>
@endsection