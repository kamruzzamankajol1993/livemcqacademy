@extends('admin.master.master')

@section('title') Exam Category Management @endsection

@section('css')
<style>
    #ajax-loaderOne { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); z-index: 1000; display: none; align-items: center; justify-content: center; }
    .sortable-list { list-style: none; padding: 0; }
    .sortable-item { background: #fff; border: 1px solid #ddd; margin-bottom: 5px; padding: 10px 15px; border-radius: 5px; cursor: move; display: flex; align-items: center; }
    .sortable-item i { margin-right: 15px; color: #ccc; }
    .ui-state-highlight { height: 45px; background: #f8f9fa; border: 1px dashed #4e73df; margin-bottom: 5px; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Exam Category</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fa fa-plus me-1"></i> Add New
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div id="ajax-loaderOne">
                <div class="spinner-border text-primary" role="status"></div>
            </div>

            <div class="card-header bg-white pb-0 border-0">
                <ul class="nav nav-tabs" id="categoryTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#listView" type="button">Category List</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="reorder-tab" data-bs-toggle="tab" data-bs-target="#reorderView" type="button">Reorder (Drag & Drop)</button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                @include('flash_message')

                <div class="tab-content">
                    {{-- Tab 1: List View --}}
                    <div class="tab-pane fade show active" id="listView">
                        <div class="row mb-3 justify-content-end">
                            <div class="col-md-3">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sl</th>
                                        <th>Name (EN)</th>
                                        <th>Name (BN)</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody"></tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div id="pagination-info"></div>
                            <nav><ul class="pagination pagination-sm mb-0" id="pagination"></ul></nav>
                        </div>
                    </div>

                    {{-- Tab 2: Reorder View --}}
                    <div class="tab-pane fade" id="reorderView">
                        <div class="alert alert-info py-2"><i class="fa fa-info-circle me-2"></i> Drag the items up or down to change their display order.</div>
                        <div id="sortable-container">
                            <ul id="sortable" class="sortable-list">
                                {{-- Loaded via AJAX --}}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- Modals (Create & Edit) --}}
@include('admin.exam_category.modals') {{-- Modal এর কোড আলাদা ফাইলে রাখা ভালো --}}

@endsection

@section('script')
{{-- External Libraries --}}
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        let currentPage = 1;
        const routes = {
            index: "{{ route('exam-category.index') }}",
            reorder: "{{ route('exam.category.serial') }}",
            edit: "{{ route('exam-category.edit', ':id') }}",
            update: "{{ route('exam-category.update', ':id') }}",
            destroy: "{{ route('exam-category.destroy', ':id') }}"
        };

        // --- ১. ডাটা লোড করার ফাংশন (List View) ---
        function fetchData() {
            $('#ajax-loaderOne').css('display', 'flex');
            let search = $('#searchInput').val();

            $.get(routes.index, { page: currentPage, search: search }, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let sl = (res.current_page - 1) * res.per_page + i + 1;
                        let statusBadge = item.status == 1 
                            ? '<span class="badge bg-success">Active</span>' 
                            : '<span class="badge bg-danger">Inactive</span>';

                        rows += `<tr>
                            <td>${sl}</td>
                            <td>${item.name_en}</td>
                            <td>${item.name_bn || '--'}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-info text-white btn-edit" data-id="${item.id}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <form action="${routes.destroy.replace(':id', item.id)}" method="POST" class="d-inline delete-form">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger btn-delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="5" class="text-center text-muted">No data found.</td></tr>';
                }
                $('#tableBody').html(rows);
                renderPagination(res);
                $('#ajax-loaderOne').hide();
            });
        }

        // --- ২. ড্র্যাগ এন্ড ড্রপ ডাটা লোড (Reorder View) ---
        function fetchReorderData() {
            $('#ajax-loaderOne').css('display', 'flex');
            $.get(routes.index, { all_data: 1 }, function(res) {
                let items = '';
                res.forEach(item => {
                    items += `<li class="sortable-item" data-id="${item.id}">
                        <i class="fa fa-bars me-3 text-muted"></i>
                        <strong>${item.name_en}</strong> ${item.name_bn ? ' — ' + item.name_bn : ''}
                    </li>`;
                });
                $('#sortable').html(items || '<li class="text-center p-3">No categories to reorder</li>');
                $('#ajax-loaderOne').hide();
            });
        }

        // --- ৩. প্যাগিনেশন রেন্ডারার ---
        function renderPagination(data) {
            let html = '';
            if(data.total > 0) $('#pagination-info').html(`Showing ${data.from} to ${data.to} of ${data.total}`);
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

        // --- ৪. ড্র্যাগ এন্ড ড্রপ ইনশিয়ালাইজেশন ---
        $("#sortable").sortable({
            placeholder: "ui-state-highlight",
            handle: ".fa-bars",
            update: function() {
                let order = [];
                $('#sortable li').each(function() { order.push($(this).data('id')); });

                $.post(routes.reorder, { _token: "{{ csrf_token() }}", order: order }, function(res) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                });
            }
        });

        // --- ৫. সুইট অ্যালার্ট ডিলিট কনফার্মেশন ---
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "Deleting this category may affect related exams!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // --- ৬. এডিট মোডাল পপুলেট ---
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            $.get(routes.edit.replace(':id', id), function(data) {
                $('#edit_name_en').val(data.name_en);
                $('#edit_name_bn').val(data.name_bn);
                $('#edit_status').val(data.status);
                $('#editForm').attr('action', routes.update.replace(':id', id));
                $('#editModal').modal('show');
            });
        });

        // --- ৭. ইভেন্ট লিসেনারস ---
        $('#list-tab').on('shown.bs.tab', fetchData);
        $('#reorder-tab').on('shown.bs.tab', fetchReorderData);
        $('#searchInput').on('keyup', function() { currentPage = 1; fetchData(); });
        $(document).on('click', '.page-link', function(e) { 
            e.preventDefault(); 
            currentPage = $(this).data('page'); 
            fetchData(); 
        });

        // Initial Load
        fetchData();
    });
</script>
@endsection