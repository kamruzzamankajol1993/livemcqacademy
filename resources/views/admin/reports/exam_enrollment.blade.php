@extends('admin.master.master')
@section('title', 'Exam Reports')

@section('css')
{{-- Select2 & DateRangePicker CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .report-nav .nav-link { color: #555; font-weight: 500; border-radius: 0; }
    .report-nav .nav-link.active { background: #1e293b; color: #fff; }
    .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #dee2e6; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid mt-3">
        {{-- ড্রপডাউন ট্যাব বিকল্প বা সরাসরি ট্যাব নেভ --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Reports Center</h4>
            <div class="dropdown">
                <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">Select Report Type</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-content {{ Route::is('reports.exams') ? 'active' : '' }}" href="{{ route('reports.exams') }}">Exam Enrollments</a></li>
                    <li><a class="dropdown-item" href="{{ route('reports.examPayments') }}">Exam Payments</a></li>
                    <li><a class="dropdown-item" href="{{ route('reports.bookPayments') }}">Book Payments</a></li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                {{-- ফিল্টার সেকশন --}}
                <div class="row g-2 mb-4 bg-light p-3 rounded">
                    <div class="col-md-3">
                        <label class="small fw-bold">Year</label>
                        <select id="yearFilter" class="form-control select2">
                            <option value="">All Years</option>
                            @for($y = date('Y'); $y >= 2024; $y--) <option value="{{$y}}">{{$y}}</option> @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Month</label>
                        <select id="monthFilter" class="form-control select2">
                            <option value="">All Months</option>
                            @foreach(range(1,12) as $m) <option value="{{$m}}">{{ date('F', mktime(0,0,0,$m,1)) }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Custom Date Range</label>
                        <input type="text" id="dateRange" class="form-control" placeholder="Select Dates">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="fetchData(1)"><i class="fa fa-filter me-1"></i>Apply</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr id="tableHead">
                                {{-- Headers will be dynamic based on report type --}}
                            </tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2();
        $('#dateRange').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear' }
        });

        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        fetchData(1);
    });

    function fetchData(page) {
        $.ajax({
            url: window.location.href,
            data: {
                page: page,
                year: $('#yearFilter').val(),
                month: $('#monthFilter').val(),
                date_range: $('#dateRange').val()
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
        @if(Route::is('reports.exams'))
            $('#tableHead').html('<th>SL</th><th>Exam Package Name</th><th>Total Enrollments</th>');
            data.forEach((item, i) => {
                html += `<tr><td>${from + i}</td><td>${item.exam_name}</td><td><span class="badge bg-primary px-3">${item.total_students} Students</span></td></tr>`;
            });
        @elseif(Route::is('reports.examPayments'))
            $('#tableHead').html('<th>SL</th><th>Student</th><th>Exam</th><th>Amount</th><th>TrxID</th><th>Date</th>');
            data.forEach((item, i) => {
                html += `<tr><td>${from + i}</td><td>${item.user.name}</td><td>${item.exam_package.exam_name}</td><td class="fw-bold">৳${item.amount}</td><td>${item.transaction_id}</td><td>${moment(item.created_at).format('DD MMM, YYYY')}</td></tr>`;
            });
        @endif
        $('#tableBody').html(html || '<tr><td colspan="6" class="text-center py-4 text-muted">No data available for selected filters.</td></tr>');
    }

    // প্যাগিনেশন রেন্ডারার (আপনার স্টুডেন্ট ইনডেক্স থেকে কপি করা)
    function renderPagination(data) {
        let html = '';
        if (data.last_page > 1) {
            html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="fetchData(${data.current_page - 1})">Prev</a></li>`;
            for (let i = 1; i <= data.last_page; i++) {
                if (i === 1 || i === data.last_page || (i >= data.current_page - 1 && i <= data.current_page + 1)) {
                    html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="fetchData(${i})">${i}</a></li>`;
                }
            }
            html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="fetchData(${data.current_page + 1})">Next</a></li>`;
        }
        $('#pagination').html(html);
    }
</script>
@endsection