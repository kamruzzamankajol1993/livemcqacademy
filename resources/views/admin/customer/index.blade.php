@extends('admin.master.master')
@section('title') Student Management @endsection

@section('css')
<style>
    /* টেবিল বাটন ডিজাইন */
    .action-btns .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px; /* আপনি চাইলে ৫০% করে সার্কেল করতে পারেন */
        transition: all 0.3s ease;
        border: none;
        margin-right: 2px;
    }

    /* হোভার ইফেক্ট */
    .action-btns .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    /* আলাদা আলাদা কালার ভেরিয়েশন */
    .btn-report { background-color: #4e73df; color: white; } /* রিপোর্ট */
    .btn-view   { background-color: #1cc88a; color: white; } /* ভিউ */
    .btn-edit   { background-color: #f6c23e; color: white; } /* এডিট */
    .btn-delete { background-color: #e74a3b; color: white; } /* ডিলিট */
    
    /* আইকন সাইজ */
    .action-btns .btn i {
        font-size: 13px;
    }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Student Management</h2>
            <a href="{{ route('student.create') }}" class="btn btn-primary text-white">
                <i class="fa fa-plus me-1"></i> Add New Student
            </a>
        </div>

        <div class="card position-relative">
            <div id="tableLoader" style="display:none; position:absolute; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:10; border-radius:10px;">
                <div class="d-flex h-100 justify-content-center align-items-center">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>

            <div class="card-body">
                @include('flash_message')

                <div class="row mb-3">
                    <div class="col-md-4 ms-auto">
                        <input class="form-control" id="searchInput" type="search" placeholder="Search Name, Email or Phone...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Sl</th>
                                <th>Name</th>
                                <th>Email/Phone</th>
                                <th>Active Package</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small" id="pagination-info"></div>
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
            $('#tableLoader').show();
            let search = $('#searchInput').val();

            $.get("{{ route('ajax.student.data') }}", { page: currentPage, search: search }, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let sl = (res.current_page - 1) * res.per_page + i + 1;
                        let status = item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        
                        // Active Package Display Logic
                        let pkgName = (item.user && item.user.active_subscription) 
                                      ? `<span class="badge bg-info text-dark">${item.user.active_subscription.package.name}</span>` 
                                      : '<span class="text-muted small">No Plan</span>';

                        let showUrl = "{{ route('student.show', ':id') }}".replace(':id', item.id);
                        let editUrl = "{{ route('student.edit', ':id') }}".replace(':id', item.id);
                        let deleteUrl = "{{ route('student.destroy', ':id') }}".replace(':id', item.id);
let reportUrl = "{{ route('students.report', ':id') }}".replace(':id', item.user_id);


                        rows += `<tr>
                            <td>${sl}</td>
                            <td><strong>${item.name}</strong></td>
                            <td>${item.email}<br><small class="text-muted">${item.phone}</small></td>
                            <td>${pkgName}</td>
                            <td>${status}</td>
                           <td class="action-btns">
        <div class="d-flex justify-content-center">
            <a href="${reportUrl}" class="btn btn-report" title="Performance Report">
                <i class="fa fa-chart-line"></i>
            </a>

            <a href="${showUrl}" class="btn btn-view" title="View Profile">
                <i class="fa fa-eye"></i>
            </a>

            <a href="${editUrl}" class="btn btn-edit" title="Edit">
                <i class="fa fa-edit"></i>
            </a>
            
            <form action="${deleteUrl}" method="POST" id="delete-form-${item.id}" class="d-inline">
                @csrf @method('DELETE')
                <button type="button" onclick="deleteConfirm(${item.id})" class="btn btn-delete" title="Delete">
                    <i class="fa fa-trash"></i>
                </button>
            </form>
        </div>
    </td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="6" class="text-center py-4">No data found</td></tr>';
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
            text: "This will remove the Student profile!",
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