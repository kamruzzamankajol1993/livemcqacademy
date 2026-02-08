@extends('admin.master.master')
@section('title', 'Exam Payment Management')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .filter-card { background: #f8f9fa; border: 1px solid #e3e6f0; }
    .status-pending { color: #f6c23e; font-weight: bold; }
    .status-completed { color: #1cc88a; font-weight: bold; }
    .status-rejected { color: #e74a3b; font-weight: bold; }
    .badge-method { background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <h4 class="mb-0">Manual Exam Payments</h4>
        </div>

        {{-- ফিল্টার সেকশন --}}
        <div class="card filter-card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="small fw-bold">Search (TrxID, Phone, Name)</label>
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by TrxID or User...">
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Status Filter</label>
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

        {{-- ডাটা টেবিল --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                @include('flash_message')
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>SL</th>
                                <th>Student Info</th>
                                <th>Exam Package</th>
                                <th>Amount & Method</th>
                                <th>Transaction Details</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            {{-- AJAX Data Load Here --}}
                        </tbody>
                    </table>
                </div>

                {{-- কাস্টম প্যাগিনেশন --}}
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
        // ১. Select2 ইনিশিয়ালাইজেশন
        $('.select2').select2({ width: '100%' });

        // ২. ডাটা ফেচ ফাংশন
        function fetchExamPayments(page = 1) {
            let params = {
                page: page,
                search: $('#searchInput').val(),
                status: $('#filter_status').val()
            };

            $.get("{{ route('admin.examPayments') }}", params, function(res) {
                renderTable(res);
                renderPagination(res);
            });
        }

        // ৩. টেবিল রেন্ডারিং
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
                            <small class="text-muted"><i class="fa fa-phone me-1"></i>${item.user_phone}</small>
                        </td>
                        <td>
                            <div class="small fw-bold">${item.exam_name}</div>
                            <small class="text-muted">ID: #${item.exam_package_id}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">${item.amount} TK</div>
                            <span class="badge badge-method">${item.payment_method}</span>
                        </td>
                        <td>
                            <div class="small fw-bold">Trx: ${item.transaction_id || 'N/A'}</div>
                            <div class="small text-primary">Sender: ${item.sender_number || 'N/A'}</div>
                        </td>
                        <td class="${statusClass}">${item.status.toUpperCase()}</td>
                        <td>
                            <form action="{{ url('exam-payments/update-status') }}/${item.id}" method="POST">
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
                rows = '<tr><td colspan="7" class="text-center py-4 text-muted">No exam payments found.</td></tr>';
            }
            $('#tableBody').html(rows);
            $('#paginationInfo').text(`Showing ${res.from || 0} to ${res.to || 0} of ${res.total} entries`);
        }

        // ৪. প্যাগিনেশন রেন্ডারিং
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

        // ৫. ফিল্টার এবং সার্চ ইভেন্ট
        $('#searchInput').on('keyup', function() { fetchExamPayments(1); });
        $('#filter_status').on('change', function() { fetchExamPayments(1); });

        // ৬. প্যাগিনেশন ক্লিক হ্যান্ডলার
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            let page = $(this).data('page');
            if(page) fetchExamPayments(page);
        });

        // ইনিশিয়াল ডাটা লোড
        fetchExamPayments();
    });
</script>
@endsection