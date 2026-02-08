@extends('admin.master.master')
@section('title', 'Student Info Report')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .active-report { background: #1e293b !important; color: #fff !important; }
    .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #dee2e6; display: flex; align-items: center; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid mt-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Student Info Reports</h4>
            <div class="dropdown">
                <button class="btn btn-dark dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                    <i class="fa fa-filter me-1"></i> Switch Report
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><a class="dropdown-item" href="{{ route('reports.exams') }}">Exam Enrollments</a></li>
                    <li><a class="dropdown-item" href="{{ route('reports.examPayments') }}">Exam Payments</a></li>
                    <li><a class="dropdown-item" href="{{ route('reports.bookPayments') }}">Book Payments</a></li>
                    <li><a class="dropdown-item" href="{{ route('reports.packageEnrollment') }}">Package Enrollments</a></li>
                    <li><a class="dropdown-item" href="{{ route('reports.packagePayments') }}">Package Payments</a></li>
                    <li><a class="dropdown-item active-report" href="{{ route('reports.studentInfo') }}">Student Info</a></li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                {{-- ফিল্টার সেকশন --}}
                <div class="row g-3 mb-4 p-3 bg-light rounded border">
                    <div class="col-md-3">
                        <label class="small fw-bold text-secondary">Reg. Year</label>
                        <select id="yearFilter" class="form-control select2">
                            <option value="">All Years</option>
                            @for($y = date('Y'); $y >= 2024; $y--) <option value="{{ $y }}">{{ $y }}</option> @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-secondary">Reg. Month</label>
                        <select id="monthFilter" class="form-control select2">
                            <option value="">All Months</option>
                            @foreach(range(1,12) as $m) <option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold text-secondary">Registration Range</label>
                        <input type="text" id="dateRange" class="form-control bg-white" readonly placeholder="Select Date Range">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100 fw-bold" onclick="fetchData(1)">Generate Report</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th>SL</th>
                                <th>Student Name</th>
                                <th>Contact Info</th>
                                <th>Class</th>
                                <th>Active Package</th>
                                <th>Reg. Date</th>
                                <th class="text-center">Status</th>
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
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });
        $('#dateRange').daterangepicker({ autoUpdateInput: false, locale: { format: 'YYYY-MM-DD' } });
        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });
        fetchData(1);
    });

    function fetchData(page) {
        $.ajax({
            url: "{{ route('reports.studentInfo') }}",
            data: { 
                page: page, 
                year: $('#yearFilter').val(), 
                month: $('#monthFilter').val(), 
                date_range: $('#dateRange').val() 
            },
            beforeSend: function() {
                $('#tableBody').html('<tr><td colspan="7" class="text-center py-5">Loading Student Data...</td></tr>');
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
                let pkgName = (item.user && item.user.active_subscription) 
                            ? `<span class="badge bg-info text-dark">${item.user.active_subscription.package.name}</span>` 
                            : '<span class="text-muted small">No Plan</span>';
                
                let statusBadge = item.status == 1 ? 'bg-success' : 'bg-danger';
                let className = (item.user && item.user.school_class) ? item.user.school_class.name_en : 'N/A';

                html += `<tr>
                    <td>${from + i}</td>
                    <td><div class="fw-bold">${item.name}</div></td>
                    <td>
                        <div class="small text-dark">${item.email}</div>
                        <div class="small text-muted">${item.phone}</div>
                    </td>
                    <td><span class="small">${className}</span></td>
                    <td>${pkgName}</td>
                    <td><span class="small">${moment(item.created_at).format('DD MMM, YYYY')}</span></td>
                    <td class="text-center"><span class="badge ${statusBadge}">${item.status == 1 ? 'ACTIVE' : 'INACTIVE'}</span></td>
                </tr>`;
            });
        } else {
            html = '<tr><td colspan="7" class="text-center py-4">No student records found.</td></tr>';
        }
        $('#tableBody').html(html);
    }

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