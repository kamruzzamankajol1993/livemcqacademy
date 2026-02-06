@extends('admin.master.master')
@section('title', 'Book Category Management')

@section('css')
<style>
    #sortable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
    #sortable li { margin: 0 3px 10px 3px; padding: 12px; font-size: 1.1em; cursor: move; display: flex; align-items: center; background: #fff; border: 1px solid #e3e6f0; border-radius: 8px; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); }
    #sortable li:hover { background: #f8f9fc; }
    .ui-state-highlight { height: 50px; background-color: #f0f1f3; border: 1px dashed #ccc; margin-bottom: 10px; border-radius: 8px; }
    .handle { color: #ccc; margin-right: 15px; cursor: move; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <h4 class="mb-0">Book Category List</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fa fa-plus-circle me-1"></i> Add New Category
            </button>
        </div>

        <div class="card shadow-sm border-0 position-relative">
            <div class="card-body">
                @include('flash_message')

                <ul class="nav nav-tabs mb-4" id="bookTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-view" type="button">
                            <i class="fa fa-list me-1"></i> List View
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="reorder-tab" data-bs-toggle="tab" data-bs-target="#reorder-view" type="button">
                            <i class="fa fa-sort me-1"></i> Reorder (Drag & Drop)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="bookTabContent">
                    {{-- 1. List View --}}
                    <div class="tab-pane fade show active" id="list-view">
                        <div class="row mb-3">
                            <div class="col-md-4 ms-auto">
                                <div class="input-group">
                                    <input type="text" id="searchInput" class="form-control" placeholder="Search categories...">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">SL</th>
                                        <th>Category Name (EN)</th>
                                        <th>Category Name (BN)</th>
                                        <th width="100">Status</th>
                                        <th width="150">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody"></tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div id="paginationInfo" class="small text-muted"></div>
                            <nav><ul class="pagination pagination-sm mb-0" id="customPagination"></ul></nav>
                        </div>
                    </div>

                    {{-- 2. Reorder View --}}
                    <div class="tab-pane fade" id="reorder-view">
                        <div class="alert alert-light border py-2 mb-3">
                            <i class="fa fa-info-circle text-primary me-2"></i> Drag the categories to reorder and click "Update Order".
                        </div>
                        <ul id="sortable"></ul>
                        <div class="text-end mt-4">
                            <button id="saveOrderBtn" class="btn btn-success px-5">Update Order</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('book-category.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="fw-bold">Name (EN) *</label><input type="text" name="name_en" class="form-control" required></div>
                    <div class="mb-3"><label class="fw-bold">Name (BN)</label><input type="text" name="name_bn" class="form-control"></div>
                    <div class="mb-3"><label class="fw-bold">Status</label><select name="status" class="form-control"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Category</button></div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="" method="POST" id="editForm" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white">Edit Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="fw-bold">Name (EN) *</label><input type="text" name="name_en" id="edit_name_en" class="form-control" required></div>
                    <div class="mb-3"><label class="fw-bold">Name (BN)</label><input type="text" name="name_bn" id="edit_name_bn" class="form-control"></div>
                    <div class="mb-3"><label class="fw-bold">Status</label><select name="status" id="edit_status" class="form-control"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-info text-white">Update Category</button></div>
            </form>
        </div>
    </div>
</main>
@endsection

@section('script')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        let currentPage = 1;
        
        // জাভাস্ক্রিপ্ট ভেরিয়েবল হিসেবে রাউটগুলো রাখা হলো
        const routes = {
            fetch: "{{ route('book-category.fetch') }}",
            reorder: "{{ route('book-category.reorder') }}",
            editBase: "{{ route('book-category.edit', ':id') }}",
            updateBase: "{{ route('book-category.update', ':id') }}",
            deleteBase: "{{ route('book-category.destroy', ':id') }}"
        };

        function fetchCategories(page = 1, search = '') {
            $.get(routes.fetch, { page: page, search: search }, function(res) {
                renderTable(res);
                renderPagination(res);
            });
        }

        function renderTable(res) {
            let rows = '';
            if(res.data.length > 0) {
                res.data.forEach((item, index) => {
                    let sl = res.from + index;
                    let deleteUrl = routes.deleteBase.replace(':id', item.id);
                    rows += `<tr>
                        <td>${sl}</td>
                        <td>${item.name_en}</td>
                        <td>${item.name_bn || '--'}</td>
                        <td><span class="badge ${item.status == 1 ? 'bg-success' : 'bg-danger'}">${item.status == 1 ? 'Active' : 'Inactive'}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-info text-white edit-btn" data-id="${item.id}"><i class="fa fa-edit"></i></button>
                                <form action="${deleteUrl}" method="POST" class="delete-form">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger confirm-delete"><i class="fa fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>`;
                });
            } else { rows = '<tr><td colspan="5" class="text-center text-muted">No data found.</td></tr>'; }
            $('#tableBody').html(rows);
            $('#paginationInfo').text(`Showing ${res.from || 0} to ${res.to || 0} of ${res.total} entries`);
        }

        function renderPagination(res) {
            let pagination = '';
            if (res.last_page > 1) {
                pagination += `<li class="page-item ${res.current_page == 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${res.current_page - 1}">Prev</a></li>`;
                for (let i = 1; i <= res.last_page; i++) {
                    if(i==1 || i==res.last_page || (i>=res.current_page-1 && i<=res.current_page+1)) {
                        pagination += `<li class="page-item ${res.current_page == i ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                    }
                }
                pagination += `<li class="page-item ${res.current_page == res.last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${res.current_page + 1}">Next</a></li>`;
            }
            $('#customPagination').html(pagination);
        }

        // --- Reorder View Logic ---
        $('#reorder-tab').on('click', function() {
            // ড্রপ-ডাউন লিস্ট লোড না করার সমস্যার সমাধান: কন্ট্রোলারে fetch কন্ডিশন ঠিক করা হয়েছে
            $.get(routes.fetch, { all_data: 1 }, function(all) {
                // কন্ট্রোলার যদি সরাসরি কাঁচা অ্যারে রিটার্ন করে
                let list = all.data ? all.data : all; 
                let listItems = '';
                list.forEach(item => {
                    listItems += `<li class="ui-state-default" data-id="${item.id}">
                        <i class="fa fa-bars handle"></i>
                        <span class="fw-bold">${item.name_en}</span> 
                        <span class="text-muted ms-2">(${item.name_bn || '---'})</span>
                    </li>`;
                });
                $('#sortable').html(listItems);
            });
        });

        $("#sortable").sortable({ placeholder: "ui-state-highlight", handle: ".handle" });

        $('#saveOrderBtn').on('click', function() {
            let order = [];
            $('#sortable li').each(function() { order.push($(this).data('id')); });
            $.post(routes.reorder, { _token: "{{ csrf_token() }}", order: order }, function(res) {
                Swal.fire('Updated!', res.message, 'success');
                fetchCategories(currentPage);
            });
        });

        // --- Edit Modal Data Populate ---
        $(document).on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            let editUrl = routes.editBase.replace(':id', id);
            let updateUrl = routes.updateBase.replace(':id', id);
            
            $.get(editUrl, function(data) {
                $('#edit_name_en').val(data.name_en);
                $('#edit_name_bn').val(data.name_bn);
                $('#edit_status').val(data.status);
                $('#editForm').attr('action', updateUrl);
                $('#editModal').modal('show');
            });
        });

        $(document).on('click', '.confirm-delete', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });

        $('#searchInput').on('keyup', function() { fetchCategories(1, $(this).val()); });
        $(document).on('click', '.page-link', function(e) { e.preventDefault(); fetchCategories($(this).data('page'), $('#searchInput').val()); });

        fetchCategories();
    });
</script>
@endsection