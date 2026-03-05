@extends('admin.master.master')

@section('title') Dashboard @endsection

@section('css')
<style>
    .summary-card { border-radius: 12px; transition: transform 0.2s; }
    .summary-card:hover { transform: translateY(-5px); }
    .summary-card-icon { 
        width: 48px; height: 48px; border-radius: 10px; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 20px; flex-shrink: 0;
    }
    .filter-btn { border: 1px solid #dee2e6; background: #fff; color: #64748b; font-size: 0.85rem; padding: 0.4rem 1rem; }
    .filter-btn.active { background: var(--primary-color); color: #fff; border-color: var(--primary-color); }
    .card-header-custom { background-color: var(--primary-color); color: white; font-weight: 600; }
    .bg-soft-purple { background-color: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
    .table-img { width: 35px; height: 45px; object-fit: cover; border-radius: 4px; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        {{-- Header & Filters --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h2 class="mb-0 fw-bold" style="color: var(--primary-color);">System Overview</h2>
            <div class="d-flex gap-1 shadow-sm rounded p-1 bg-white">
                <a href="{{ route('home', ['filter' => 'today']) }}" class="btn filter-btn {{ $filter == 'today' ? 'active' : '' }}">Today</a>
                <a href="{{ route('home', ['filter' => 'this_month']) }}" class="btn filter-btn {{ $filter == 'this_month' ? 'active' : '' }}">This Month</a>
                <a href="{{ route('home', ['filter' => 'this_year']) }}" class="btn filter-btn {{ $filter == 'this_year' ? 'active' : '' }}">This Year</a>
            </div>
        </div>

        {{-- Row 1: Core Metrics (Updated with New Students) --}}
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0 summary-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="summary-card-icon bg-primary text-white me-3"><i data-feather="book-open"></i></div>
                        <div><h6 class="text-muted small mb-1">Total Books</h6><h4 class="mb-0">{{ number_format($totalBooks) }}</h4></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0 summary-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="summary-card-icon bg-info text-white me-3"><i data-feather="file-text"></i></div>
                        <div><h6 class="text-muted small mb-1">Total MCQs</h6><h4 class="mb-0">{{ number_format($totalMcq) }}</h4></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0 summary-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="summary-card-icon bg-soft-purple me-3"><i data-feather="users"></i></div>
                        <div><h6 class="text-muted small mb-1">New Students (7d)</h6><h4 class="mb-0">+{{ number_format($newStudentsCount) }}</h4></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0 summary-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="summary-card-icon bg-warning text-white me-3"><i data-feather="dollar-sign"></i></div>
                        <div><h6 class="text-muted small mb-1">Total Earnings</h6><h4 class="mb-0">৳{{ number_format($totalEarnings) }}</h4></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Expiring Soon Alert --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="mb-0 text-danger fw-bold"><i class="fa fa-clock me-2"></i>Expiring Soon (Within 7 Days)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr><th class="ps-3">Student Name</th><th>Package</th><th>Expiry Date</th><th class="text-end pe-3">Action</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($expiringSoon as $sub)
                                    <tr>
                                        <td class="ps-3"><strong>{{ $sub->user->name }}</strong><br><small class="text-muted">{{ $sub->user->phone }}</small></td>
                                        <td><span class="badge bg-soft-primary text-primary border">{{ $sub->package->name }}</span></td>
                                        <td class="text-danger fw-bold">{{ $sub->end_date->format('d M, Y') }}</td>
                                        <td class="text-end pe-3"><a href="{{ route('student.show', $sub->user->customer->id ?? 0) }}" class="btn btn-sm btn-outline-primary">Profile</a></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No subscriptions expiring soon.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Recent Transactions & Top Packages (NEW) --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between">
                        <h5 class="mb-0 fw-bold" style="color: var(--primary-color);">Recent Transactions</h5>
                        <span class="badge bg-success">Success</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th class="ps-3">Student</th><th>Package</th><th>Amount</th><th>Method</th><th class="text-end pe-3">Time</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPayments as $pay)
                                    <tr>
                                        <td class="ps-3"><strong>{{ $pay->user->name ?? 'N/A' }}</strong></td>
                                        <td><small class="badge border text-dark">{{ $pay->package->name ?? 'N/A' }}</small></td>
                                        <td class="fw-bold text-success">৳{{ number_format($pay->amount) }}</td>
                                        <td>{{ $pay->payment_method }}</td>
                                        <td class="text-end pe-3 small text-muted">{{ $pay->created_at->diffForHumans() }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-0"><h5 class="mb-0 fw-bold text-center">Top Selling Packages</h5></div>
                    <div class="card-body">
                        @foreach($topPackages as $pkg)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-bold">{{ $pkg->name }}</span>
                                <span class="small text-muted">{{ $pkg->total }} Sales</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                @php $percent = ($pkg->total / ($topPackages->sum('total') ?: 1)) * 100; @endphp
                                <div class="progress-bar bg-primary" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 4: Charts --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-7"><div class="card h-100 shadow-sm border-0"><div class="card-header card-header-custom">MCQ Upload History</div><div class="card-body"><div id="mcq_chart" style="width: 100%; height: 350px;"></div></div></div></div>
            <div class="col-lg-5"><div class="card h-100 shadow-sm border-0"><div class="card-header card-header-custom">Questions by Subject</div><div class="card-body"><div id="subject_chart" style="width: 100%; height: 350px;"></div></div></div></div>
        </div>

        {{-- Row 5: Recent Data Tables --}}
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold" style="color: var(--primary-color);">Recently Added Books</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr><th class="ps-3">Book</th><th>Subject</th><th>Type</th></tr></thead>
                                <tbody>
                                    @foreach($recentBooks as $book)
                                    <tr>
                                        <td class="ps-3"><div class="d-flex align-items-center"><img src="{{ asset('public/'.$book->image) }}" class="table-img me-2 shadow-sm" onerror="this.src='https://via.placeholder.com/35x45'"><div><div class="fw-bold small">{{ $book->title }}</div><small class="text-muted" style="font-size: 10px;">{{ $book->isbn_code }}</small></div></div></td>
                                        <td class="small">{{ $book->subject->name_en ?? 'N/A' }}</td>
                                        <td><span class="badge {{ $book->type == 'free' ? 'bg-success' : 'bg-primary' }} small">{{ strtoupper($book->type) }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold" style="color: var(--primary-color);">Recent MCQs</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr><th class="ps-3">Question</th><th>Class</th><th>Status</th></tr></thead>
                                <tbody>
                                    @foreach($recentMcqs as $mcq)
                                    <tr>
                                        <td class="ps-3"><div class="text-truncate" style="max-width: 200px;" title="{{ strip_tags($mcq->question) }}">{{ strip_tags($mcq->question) }}</div></td>
                                        <td><span class="badge bg-light text-dark border">{{ $mcq->class->name_en ?? 'N/A' }}</span></td>
                                        <td><i class="fa fa-circle {{ $mcq->status == 1 ? 'text-success' : 'text-danger' }} me-1" style="font-size: 8px;"></i> {{ $mcq->status == 1 ? 'Active' : 'Inactive' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawCharts);

    function drawCharts() {
        var mcqData = google.visualization.arrayToDataTable(@json($mcqChartData));
        var mcqOptions = { colors: ['#1e2939'], legend: { position: 'none' }, chartArea: { width: '85%', height: '70%' }, vAxis: { gridlines: { color: '#f1f5f9' } } };
        new google.visualization.ColumnChart(document.getElementById('mcq_chart')).draw(mcqData, mcqOptions);

        var subData = google.visualization.arrayToDataTable(@json($subjectChartData));
        var subOptions = { pieHole: 0.4, colors: ['#1e2939', '#00c951', '#3b82f6', '#f59e0b', '#ef4444'], chartArea: { width: '90%', height: '80%' } };
        new google.visualization.PieChart(document.getElementById('subject_chart')).draw(subData, subOptions);
    }
    $(window).resize(function(){ drawCharts(); });
</script>
@endsection