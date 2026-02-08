@extends('admin.master.master')

@section('title') Exam Package Management | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<style>
    #ajax-loaderOne {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.8); z-index: 1000; display: none;
        align-items: center; justify-content: center; border-radius: 0.375rem;
        backdrop-filter: blur(1px);
    }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Exam Package List</h2>
            {{-- সরাসরি ক্রিয়েট পেজে যাওয়ার জন্য A ট্যাগ --}}
            <a href="{{ route('exam-package.create') }}" class="btn btn-primary">
                <i class="fa fa-plus-circle me-1"></i> Create New Package
            </a>
        </div>

        <div class="card shadow-sm border-0 position-relative">
            {{-- Loader --}}
            <div id="ajax-loaderOne">
                <div class="spinner-border text-primary" role="status"></div>
            </div>

            <div class="card-body">
                @include('flash_message')

                <div class="row mb-3 justify-content-end">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by Exam Name...">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="50">Sl</th>
                                <th>Exam Name</th>
                                <th>Class & Department</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Validity</th>
                                <th>Status</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            {{-- AJAX Data --}}
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small" id="pagination-info"></div>
                    <nav><ul class="pagination pagination-sm mb-0" id="pagination"></ul></nav>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        let currentPage = 1;
        const routes = {
            fetch: "{{ route('exam-package.index') }}",
            edit: "{{ route('exam-package.edit', ':id') }}",
             show: "{{ route('exam-package.show', ':id') }}",
            destroy: "{{ route('exam-package.destroy', ':id') }}"
        };

        // --- 1. Fetch List Data ---
        function fetchData() {
            $('#ajax-loaderOne').css('display', 'flex');
            let search = $('#searchInput').val();

            $.get(routes.fetch, { page: currentPage, search: search }, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let sl = (res.current_page - 1) * res.per_page + i + 1;
                        let className = item.school_class ? item.school_class.name_en : '--';
                        let deptName = item.department ? ` (${item.department.name_en})` : '';
                        let typeBadge = item.exam_type === 'free' ? '<span class="badge bg-success">Free</span>' : '<span class="badge bg-primary">Paid</span>';
                        let statusBadge = item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        let categoryName = item.category ? item.category.name_en : '--';
                        // এডিট লিঙ্কের জন্য ডাইনামিক ইউআরএল জেনারেট করা
                        let editUrl = routes.edit.replace(':id', item.id);
let showUrl = routes.show.replace(':id', item.id);
                        rows += `<tr>
                            <td>${sl}</td>
                            <td>
        <strong>${item.exam_name}</strong><br>
        <small class="text-muted">Cat: ${categoryName}</small>
    </td>
                            <td>${className}${deptName}</td>
                            <td>${typeBadge}</td>
                            <td>${item.price > 0 ? item.price + ' TK' : '--'}</td>
                            <td>${item.validity_days} Days</td>
                            <td>${statusBadge}</td>
                            <td>
        <div class="d-flex gap-1">
            {{-- Show Button --}}
            <a href="${showUrl}" class="btn btn-sm btn-warning text-white" title="Show Details">
                <i class="fa fa-eye"></i>
            </a>

            {{-- Edit Button --}}
            <a href="${editUrl}" class="btn btn-sm btn-info text-white" title="Edit">
                <i class="fa fa-edit"></i>
            </a>

            {{-- Delete Button --}}
            <form action="${routes.destroy.replace(':id', item.id)}" method="POST" class="d-inline delete-form">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-sm btn-danger btn-delete" title="Delete">
                    <i class="fa fa-trash"></i>
                </button>
            </form>
        </div>
    </td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="8" class="text-center text-muted">No exam packages found.</td></tr>';
                }
                $('#tableBody').html(rows);
                renderPagination(res);
                $('#ajax-loaderOne').hide();
            }).fail(function() {
                $('#ajax-loaderOne').hide();
            });
        }

        // --- 2. Custom Pagination ---
        function renderPagination(data) {
            let html = '';
            if(data.total > 0) $('#pagination-info').html(`Showing ${data.from} to ${data.to} of ${data.total} exam packages`);
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

        // --- 3. SweetAlert2 Delete ---
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "This exam package and its settings will be deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // --- 4. Events ---
        fetchData();
        $('#searchInput').on('keyup', function() { currentPage = 1; fetchData(); });
        $(document).on('click', '.page-link', function(e) { 
            e.preventDefault(); 
            currentPage = $(this).data('page'); 
            fetchData(); 
        });
    });
</script>
@endsection