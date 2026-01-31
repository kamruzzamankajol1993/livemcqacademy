@extends('admin.master.master')

@section('title') Feature List | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<style>
    #tableLoader {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.8); z-index: 50; display: none;
        align-items: center; justify-content: center;
    }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Feature List</h2>
            <a href="{{ route('feature-list.create') }}" class="btn btn-primary text-white">
                <i class="fa fa-plus me-1"></i> Add New Feature
            </a>
        </div>

        <div class="card position-relative">
            <div id="tableLoader">
                <div class="spinner-border text-primary" role="status"></div>
            </div>

            <div class="card-body">
                @include('flash_message')

                <div class="row mb-3">
                    <div class="col-md-4 ms-auto">
                        <input class="form-control" id="searchInput" type="search" placeholder="Search Name or Code...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Name</th>
                                <th>System Code</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted" id="pagination-info"></div>
                    <nav><ul class="pagination mb-0" id="pagination"></ul></nav>
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

        function fetchData() {
            $('#tableLoader').css('display', 'flex');
            let search = $('#searchInput').val();

            $.get("{{ route('ajax.feature_list.data') }}", { page: currentPage, search: search }, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let sl = (res.current_page - 1) * res.per_page + i + 1;
                        let status = item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        
                        let editUrl = "{{ route('feature-list.edit', ':id') }}".replace(':id', item.id);
                        let deleteUrl = "{{ route('feature-list.destroy', ':id') }}".replace(':id', item.id);

                        rows += `<tr>
                            <td>${sl}</td>
                            <td>${item.name}</td>
                            <td><code class="text-primary">${item.code}</code></td>
                            <td>${status}</td>
                            <td>
                                <a href="${editUrl}" class="btn btn-sm btn-info text-white"><i class="fa fa-edit"></i></a>
                                <form action="${deleteUrl}" method="POST" class="d-inline delete-form" id="delete-form-${item.id}">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="deleteConfirm(${item.id})" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="5" class="text-center text-muted">No data found</td></tr>';
                }
                $('#tableBody').html(rows);
                renderPagination(res);
                $('#tableLoader').hide();
            });
        }

        function renderPagination(data) {
            let html = '';
            $('#pagination-info').html(`Showing ${data.from || 0} to ${data.to || 0} of ${data.total} entries`);

            if(data.last_page > 1) {
                html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" data-page="${data.current_page - 1}">Prev</a></li>`;
                for(let i=1; i<=data.last_page; i++) {
                    if(i==1 || i==data.last_page || (i>=data.current_page-1 && i<=data.current_page+1)) {
                        html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" data-page="${i}">${i}</a></li>`;
                    }
                }
                html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" data-page="${data.current_page + 1}">Next</a></li>`;
            }
            $('#pagination').html(html);
        }

        $(document).on('click', '.page-link', function() {
            currentPage = $(this).data('page');
            fetchData();
        });

        $('#searchInput').on('keyup', function() {
            currentPage = 1;
            fetchData();
        });

        fetchData();
    });

    function deleteConfirm(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection