@extends('admin.master.master')
@section('title', 'Book Payment Management')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .filter-card { background: #f8f9fa; border: 1px solid #e3e6f0; }
    .status-pending { color: #f6c23e; font-weight: bold; }
    .status-completed { color: #1cc88a; font-weight: bold; }
    .status-rejected { color: #e74a3b; font-weight: bold; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <h4 class="mb-0">Manual Book Payments</h4>
        </div>

        {{-- ফিল্টার সেকশন --}}
        <div class="card filter-card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="small fw-bold">Search (TrxID, Phone, Name)</label>
                        <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Status</label>
                        <select id="filter_status" class="form-control select2">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                @include('flash_message')
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>SL</th>
                                <th>User Info</th>
                                <th>Book Title</th>
                                <th>Payment Details</th>
                                <th>TrxID & Sender</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            {{-- AJAX Data Load --}}
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div id="paginationInfo" class="small text-muted"></div>
                    <ul class="pagination pagination-sm mb-0" id="customPagination"></ul>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        function fetchPayments(page = 1) {
            let params = {
                page: page,
                search: $('#searchInput').val(),
                status: $('#filter_status').val()
            };

            $.get("{{ route('admin.bookPayments') }}", params, function(res) {
                renderTable(res);
                renderPagination(res);
            });
        }

        function renderTable(res) {
            let rows = '';
            if (res.data.length > 0) {
                res.data.forEach((item, index) => {
                    let sl = res.from + index;
                    let statusClass = `status-${item.status}`;
                    
                    rows += `<tr>
                        <td>${sl}</td>
                        <td>
                            <div class="fw-bold">${item.user_name}</div>
                            <small class="text-muted">${item.user_phone}</small>
                        </td>
                        <td>${item.book_title}</td>
                        <td>
                            <div>${item.amount} TK</div>
                            <small class="badge bg-light text-dark border">${item.payment_method}</small>
                        </td>
                        <td>
                            <div class="small fw-bold">${item.transaction_id || 'N/A'}</div>
                            <div class="small text-primary">${item.sender_number || ''}</div>
                        </td>
                        <td class="${statusClass}">${item.status.toUpperCase()}</td>
                        <td>
                            <form action="{{ url('book-payments/update-status') }}/${item.id}" method="POST" class="status-form">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                    <option value="pending" ${item.status === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="completed" ${item.status === 'completed' ? 'selected' : ''}>Approve</option>
                                    <option value="rejected" ${item.status === 'rejected' ? 'selected' : ''}>Reject</option>
                                </select>
                            </form>
                        </td>
                    </tr>`;
                });
            } else {
                rows = '<tr><td colspan="7" class="text-center py-4">No payments found.</td></tr>';
            }
            $('#tableBody').html(rows);
            $('#paginationInfo').text(`Showing ${res.from || 0} to ${res.to || 0} of ${res.total} entries`);
        }

        function renderPagination(res) {
            let pagination = '';
            if (res.last_page > 1) {
                pagination += `<li class="page-item ${res.current_page == 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${res.current_page - 1}">Prev</a></li>`;
                for (let i = 1; i <= res.last_page; i++) {
                    pagination += `<li class="page-item ${res.current_page == i ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }
                pagination += `<li class="page-item ${res.current_page == res.last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${res.current_page + 1}">Next</a></li>`;
            }
            $('#customPagination').html(pagination);
        }

        $('#searchInput').on('keyup', function() { fetchPayments(1); });
        $('#filter_status').on('change', function() { fetchPayments(1); });
        $(document).on('click', '.page-link', function(e) { e.preventDefault(); fetchPayments($(this).data('page')); });

        fetchPayments();
    });
</script>
@endsection