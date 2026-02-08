@extends('admin.master.master')
@section('title', 'Exam Payment Reports')

@section('css')
{{-- Select2 & DateRangePicker CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    /* ট্যাব এবং ড্রপডাউন ডিজাইন */
    .report-header { background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 20px; shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #dee2e6; display: flex; align-items: center; }
    .table-light th { font-weight: 600; color: #475569; text-transform: uppercase; font-size: 12px; }
    .active-report { background: #1e293b !important; color: #fff !important; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid mt-3">
        {{-- রিপোর্ট সিলেকশন ড্রপডাউন --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Exam Payment Reports</h4>
            <div class="dropdown">
                <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fa fa-list-alt me-1"></i> Switch Report
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('reports.exams') }}">Exam Enrollments</a></li>
                    <li><a class="dropdown-item active-report" href="{{ route('reports.examPayments') }}">Exam Payments</a></li>
                    <li><a class="dropdown-item" href="{{ route('reports.bookPayments') }}">Book Payments</a></li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                {{-- ফিল্টার সেকশন --}}
                <div class="row g-3 mb-4 p-3 bg-light rounded shadow-sm">
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted">Filter by Year</label>
                        <select id="yearFilter" class="form-control select2">
                            <option value="">All Years</option>
                            @for($y = date('Y'); $y >= 2024; $y--) 
                                <option value="{{ $y }}">{{ $y }}</option> 
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted">Filter by Month</label>
                        <select id="monthFilter" class="form-control select2">
                            <option value="">All Months</option>
                            @foreach(range(1,12) as $m) 
                                <option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option> 
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-muted">Custom Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa fa-calendar"></i></span>
                            <input type="text" id="dateRange" class="form-control bg-white" readonly placeholder="Select Date Range">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100 fw-bold" onclick="fetchData(1)">
                            <i class="fa fa-search me-1"></i> Search
                        </button>
                    </div>
                </div>

                {{-- ডাটা টেবিল --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">SL</th>
                                <th>Student Info</th>
                                <th>Exam Package</th>
                                <th>Payment Method</th>
                                <th>Amount</th>
                                <th>Transaction ID</th>
                                <th>Date & Time</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            {{-- AJAX Data --}}
                        </tbody>
                    </table>
                </div>

                {{-- প্যাগিনেশন --}}
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div id="paginationInfo" class="small text-muted"></div>
                    <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
{{-- JS Libraries --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        // DateRangePicker Initialization
        $('#dateRange').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
        });

        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Initial Data Fetch
        fetchData(1);
    });

    function fetchData(page) {
        // লডার শো করা যেতে পারে
        $.ajax({
            url: "{{ route('reports.examPayments') }}",
            data: {
                page: page,
                year: $('#yearFilter').val(),
                month: $('#monthFilter').val(),
                date_range: $('#dateRange').val()
            },
            beforeSend: function() {
                $('#tableBody').html('<tr><td colspan="8" class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</td></tr>');
            },
            success: function(res) {
                renderTable(res.data, res.from);
                renderPagination(res);
                $('#paginationInfo').text(`Showing ${res.from || 0} to ${res.to || 0} of ${res.total} entries`);
            }
        });
    }

    function renderTable(data, from) {
        let html = '';
        if (data.length > 0) {
            data.forEach((item, i) => {
                // পেমেন্ট স্ট্যাটাস কালার লজিক
                let statusBadge = item.status === 'success' ? 'bg-success' : (item.status === 'pending' ? 'bg-warning text-dark' : 'bg-danger');
                
                html += `<tr>
                    <td>${from + i}</td>
                    <td>
                        <div class="fw-bold">${item.user ? item.user.name : 'N/A'}</div>
                        <small class="text-muted">${item.user ? item.user.phone : ''}</small>
                    </td>
                    <td><span class="small">${item.exam_package ? item.exam_package.exam_name : 'N/A'}</span></td>
                    <td><span class="badge border text-dark bg-light">${item.payment_method || 'N/A'}</span></td>
                    <td class="fw-bold text-primary">৳${item.amount}</td>
                    <td class="small text-muted">${item.transaction_id || 'N/A'}</td>
                    <td>
                        <div class="small">${moment(item.created_at).format('DD MMM, YYYY')}</div>
                        <div class="text-muted" style="font-size: 11px;">${moment(item.created_at).format('hh:mm A')}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge ${statusBadge} px-2">${item.status ? item.status.toUpperCase() : 'N/A'}</span>
                    </td>
                </tr>`;
            });
        } else {
            html = '<tr><td colspan="8" class="text-center py-5 text-muted">No payment records found for the selected criteria.</td></tr>';
        }
        $('#tableBody').html(html);
    }

    // আপনার প্রজেক্টের স্টাইল অনুযায়ী কাস্টম প্যাগিনেশন
    function renderPagination(data) {
        let html = '';
        if (data.last_page > 1) {
            html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="fetchData(${data.current_page - 1})">Prev</a>
                     </li>`;
            
            for (let i = 1; i <= data.last_page; i++) {
                if (i === 1 || i === data.last_page || (i >= data.current_page - 1 && i <= data.current_page + 1)) {
                    html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                                <a class="page-link" href="javascript:void(0)" onclick="fetchData(${i})">${i}</a>
                             </li>`;
                } else if (i === data.current_page - 2 || i === data.current_page + 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="fetchData(${data.current_page + 1})">Next</a>
                     </li>`;
        }
        $('#pagination').html(html);
    }
</script>
@endsection