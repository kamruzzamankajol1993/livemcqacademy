@extends('admin.master.master')

@section('title') Dashboard @endsection

@section('css')
<style>
    /* --- Keep Original Styles --- */
    .card-icon { display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; border-radius: 50%; color: white; }
    .card-header-custom { background-color: var(--primary-color); color: white; border-bottom: none; }
    
    /* --- NEW STYLES (From your previous code) --- */
    .filter-btn { border-radius: 0.25rem; border: 1px solid #dee2e6; background-color: #fff; color: #6c757d; padding: 0.375rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease-in-out; }
    .filter-btn:hover { background-color: #f8f9fa; text-decoration: none; }
    .filter-btn.active { background-color: #1e2939; color: #fff; border-color: #1e2939; }
    
    .summary-card .card-body { padding: 1.25rem; }
    .summary-card-icon { flex-shrink: 0; margin-right: 1rem; }
    .summary-card-icon i { width: 32px; height: 32px; stroke-width: 2.5; }
    .summary-card h6 { font-size: 0.85rem; margin-bottom: 0.25rem !important; }
    .summary-card h4 { font-size: 1.35rem; font-weight: 600; margin-bottom: 0 !important; }
    
    .question-preview { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
@endsection

@section('body')
 <main class="main-content">
        <div class="container-fluid">
            {{-- Header & Filters --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <h2 class="mb-0">Overview</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('home', ['filter' => 'today']) }}" class="btn filter-btn @if($filter == 'today') active @endif">Today</a>
                    <a href="{{ route('home', ['filter' => 'this_month']) }}" class="btn filter-btn @if($filter == 'this_month') active @endif">This Month</a>
                    <a href="{{ route('home', ['filter' => 'this_year']) }}" class="btn filter-btn @if($filter == 'this_year') active @endif">This Year</a>
                </div>
            </div>

            {{-- Row 1: Key Metrics (Replaces Financial Data) --}}
            <div class="row g-4 mb-4">
                {{-- Total MCQs --}}
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="summary-card-icon text-primary">
                                <i data-feather="file-text"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Questions (MCQ)</h6>
                                <h4 class="mb-0">{{ number_format($totalMcq) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Total Subjects --}}
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="summary-card-icon text-danger">
                                <i data-feather="book"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Subjects</h6>
                                <h4 class="mb-0">{{ $totalSubjects }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Total Classes --}}
                 <div class="col-md-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="summary-card-icon text-success">
                                <i data-feather="layers"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Classes</h6>
                                <h4 class="mb-0">{{ $totalClasses }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Total Institutes --}}
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="summary-card-icon text-warning">
                                <i data-feather="home"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Institutes</h6>
                                <h4 class="mb-0">{{ $totalInstitutes }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2: Secondary Metrics --}}
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="summary-card-icon text-info">
                                <i data-feather="upload-cloud"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">New Questions ({{ ucfirst(str_replace('_', ' ', $filter)) }})</h6>
                                <h4 class="mb-0">{{ $newQuestionsCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Placeholder for future metrics --}}
                <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 bg-white p-3">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded p-3 me-3"><i class="fa fa-users"></i></div>
                <div>
                    <h6 class="text-muted mb-0">Total Customers</h6>
                    <h3 class="mb-0">{{ $totalCustomers }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 bg-white p-3">
            <div class="d-flex align-items-center">
                <div class="bg-success text-white rounded p-3 me-3"><i class="fa fa-shopping-cart"></i></div>
                <div>
                    <h6 class="text-muted mb-0">Active Subscriptions</h6>
                    <h3 class="mb-0">{{ $activeSubscriptions }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 bg-white p-3">
            <div class="d-flex align-items-center">
                <div class="bg-info text-white rounded p-3 me-3"><i class="fa fa-money-bill"></i></div>
                <div>
                    <h6 class="text-muted mb-0">Total Earnings</h6>
                    <h3 class="mb-0">৳{{ number_format($totalEarnings, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
            </div>

            {{-- Row 3: Charts --}}
            <div class="row g-4 mb-4">
                {{-- Upload History Chart --}}
                <div class="col-lg-7">

                    <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="mb-0 text-danger fw-bold"><i class="fa fa-exclamation-circle me-2"></i>Expiring Soon (Within 7 Days)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Customer</th>
                        <th>Package</th>
                        <th>Expire Date</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expiringSoon as $sub)
                    <tr>
                        <td class="ps-3">{{ $sub->user->name }}<br><small class="text-muted">{{ $sub->user->phone }}</small></td>
                        <td><span class="badge bg-soft-primary text-primary">{{ $sub->package->name }}</span></td>
                        <td>{{ $sub->end_date->format('d M, Y') }}</td>
                        <td class="text-end pe-3">
                            @if($sub->user->customer)
                            <a href="{{ route('customer.show', $sub->user->customer->id) }}" class="btn btn-sm btn-outline-primary">View Profile</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4">No data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
                    <div class="card h-100">
                        <div class="card-header card-header-custom">
                            Questions Upload Trend
                        </div>
                        <div class="card-body">
                            <div id="mcq_chart" style="width: 100%; height: 350px;"></div>
                        </div>
                    </div>
                </div>
                {{-- Subject Distribution Chart --}}
                <div class="col-lg-5">
                    <div class="card h-100">
                        <div class="card-header card-header-custom">
                            Questions by Subject
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <div id="subject_chart" style="width: 100%; height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 4: Tables --}}
            <div class="row g-4">
                {{-- Recently Added MCQs --}}
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Recently Added MCQs</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Question</th>
                                            <th>Class</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentMcqs as $mcq)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="question-preview" title="{{ strip_tags($mcq->question) }}">
                                                        {{ strip_tags($mcq->question) }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-light text-dark border">{{ $mcq->class->name_en ?? 'N/A' }}</span></td>
                                            <td>{{ $mcq->subject->name_en ?? 'N/A' }}</td>
                                            <td>
                                                @if($mcq->status == 1)
                                                    <span class="badge rounded-pill bg-success-soft text-success">Active</span>
                                                @else
                                                    <span class="badge rounded-pill bg-danger-soft text-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No questions found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Top Classes Table --}}
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Top Classes (By Data)</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Class Name</th>
                                            <th class="text-end">Total Qns</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topClasses as $cls)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $cls->name_en }}</div>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-primary">{{ $cls->total }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No data found.</td>
                                        </tr>
                                        @endforelse
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
        drawMcqChart();
        drawSubjectChart();
    }

    function drawMcqChart() {
        // Data from Controller
        var data = google.visualization.arrayToDataTable(@json($mcqChartData));

        var options = {
            'hAxis': {title: 'Month'},
            'vAxis': {title: 'Questions Uploaded', minValue: 0},
            'legend': { position: 'none' },
            'colors': ['#1e2939'], // Using Primary Color
            'height': 350,
            'animation': { startup: true, duration: 1000, easing: 'out' }
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('mcq_chart'));
        chart.draw(data, options);
    }

    function drawSubjectChart() {
        // Data from Controller
        var data = google.visualization.arrayToDataTable(@json($subjectChartData));

        var options = {
            is3D: true,
            'height': 300,
            'colors': ['#00c951', '#1e2939', '#f1c40f', '#e74c3c', '#3498db'], // Custom Palette
            'chartArea': {'width': '90%', 'height': '80%'}
        };

        var chart = new google.visualization.PieChart(document.getElementById('subject_chart'));
        chart.draw(data, options);
    }

    $(window).resize(function(){
        drawCharts();
    });
</script>
@endsection