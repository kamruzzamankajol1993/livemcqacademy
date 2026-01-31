@extends('admin.master.master')

@section('title') Package List | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<style>
    #tableLoader {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.8); z-index: 50; display: none;
        align-items: center; justify-content: center;
        border-radius: 0.375rem;
    }
    .package-name { font-weight: 600; color: #333; }
    .price-tag { color: #28a745; font-weight: bold; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Subscription Packages</h2>
            <a href="{{ route('package.create') }}" class="btn btn-primary text-white shadow-sm">
                <i class="fa fa-plus me-1"></i> Add New Package
            </a>
        </div>

        <div class="card position-relative">
            {{-- Loader --}}
            <div id="tableLoader">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div class="card-body">
                @include('flash_message')

                <div class="row mb-3">
                    <div class="col-md-4 ms-auto">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i data-feather="search" style="width: 16px;"></i>
                            </span>
                            <input class="form-control border-start-0" id="searchInput" type="search" placeholder="Search Package Name...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Sl</th>
                                <th>Package Name</th>
                                <th>Type</th>
                                <th>Original Price</th>
                                <th>Sale Price</th>
                                <th>Popular</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            {{-- Data loaded via AJAX --}}
                        </tbody>
                    </table>
                </div>
                
                {{-- Custom Pagination --}}
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small" id="pagination-info"></div>
                    <nav>
                        <ul class="pagination mb-0" id="pagination">
                            {{-- Pagination links loaded via JS --}}
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        let currentPage = 1;

        // Initialize Feather Icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        function fetchData() {
            $('#tableLoader').css('display', 'flex');
            let search = $('#searchInput').val();

            $.get("{{ route('ajax.package.data') }}", { 
                page: currentPage, 
                search: search 
            }, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let sl = (res.current_page - 1) * res.per_page + i + 1;
                        let status = item.status == 1 
                            ? '<span class="badge bg-success">Active</span>' 
                            : '<span class="badge bg-secondary">Inactive</span>';
                        
                        let popular = item.is_popular == 1 
                            ? '<span class="badge bg-warning text-dark"><i class="fa fa-star"></i> Popular</span>' 
                            : '<span class="text-muted">--</span>';
let showUrl = "{{ route('package.show', ':id') }}".replace(':id', item.id);
                        let editUrl = "{{ route('package.edit', ':id') }}".replace(':id', item.id);
                        let deleteUrl = "{{ route('package.destroy', ':id') }}".replace(':id', item.id);

                        rows += `<tr>
                            <td>${sl}</td>
                            <td class="package-name">${item.name}</td>
                            <td class="text-capitalize">${item.type}</td>
                            <td class="text-decoration-line-through text-muted">${item.original_price}</td>
                            <td class="price-tag">${item.price}</td>
                            <td>${popular}</td>
                            <td>${status}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="${showUrl}" class="btn btn-sm btn-primary" title="View Details">
                <i class="fa fa-eye"></i>
            </a>
                                    <a href="${editUrl}" class="btn btn-sm btn-info text-white" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="${deleteUrl}" method="POST" id="delete-form-${item.id}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="deleteConfirm(${item.id})" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="8" class="text-center text-muted py-4">No packages found</td></tr>';
                }
                
                $('#tableBody').html(rows);
                renderPagination(res);
                $('#tableLoader').hide();
            }).fail(function() {
                $('#tableLoader').hide();
                toastr.error('Failed to load data.');
            });
        }

        function renderPagination(data) {
            let html = '';
            // Update info text
            if(data.total > 0) {
                $('#pagination-info').html(`Showing ${data.from} to ${data.to} of ${data.total} entries`);
            } else {
                $('#pagination-info').html('');
            }

            if(data.last_page > 1) {
                // Prev Button
                html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="javascript:void(0)" data-page="${data.current_page - 1}">Prev</a>
                         </li>`;

                // Page Numbers
                for(let i=1; i<=data.last_page; i++) {
                    if(i==1 || i==data.last_page || (i>=data.current_page-1 && i<=data.current_page+1)) {
                        html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                                    <a class="page-link" href="javascript:void(0)" data-page="${i}">${i}</a>
                                 </li>`;
                    } else if (i == data.current_page - 2 || i == data.current_page + 2) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                }

                // Next Button
                html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                            <a class="page-link" href="javascript:void(0)" data-page="${data.current_page + 1}">Next</a>
                         </li>`;
            }
            $('#pagination').html(html);
        }

        // Pagination Click Event
        $(document).on('click', '.page-link', function() {
            let page = $(this).data('page');
            if(page) {
                currentPage = page;
                fetchData();
            }
        });

        // Search Input Event
        $('#searchInput').on('keyup', function() {
            currentPage = 1;
            fetchData();
        });

        // Initial Fetch
        fetchData();
    });

    // SweetAlert Delete Confirmation
    function deleteConfirm(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This package and its related settings will be removed!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection