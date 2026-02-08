@extends('admin.master.master')
@section('title', 'Exam Leaderboard | ' . $package->exam_name)

@section('css')
<style>
    #ajax-loader-leaderboard {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.8); z-index: 1000; display: none;
        align-items: center; justify-content: center; border-radius: 0.375rem;
    }
    .rank-1 { background-color: #ffd700 !important; color: #000; font-weight: bold; }
    .rank-2 { background-color: #c0c0c0 !important; color: #000; font-weight: bold; }
    .rank-3 { background-color: #cd7f32 !important; color: #000; font-weight: bold; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <h4 class="mb-0"><i class="fa fa-trophy text-warning me-2"></i> Leaderboard: {{ $package->exam_name }}</h4>
            <a href="{{ route('exam-package.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Back to Packages
            </a>
        </div>

        <div class="card shadow-sm border-0 position-relative">
            {{-- Loader --}}
            <div id="ajax-loader-leaderboard">
                <div class="spinner-border text-primary" role="status"></div>
            </div>

            <div class="card-body">
                {{-- সার্চ বক্স --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search by Student Name or Phone...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">Rank</th>
                                <th>Student Information</th>
                                <th class="text-center">Score (Earned/Total)</th>
                                <th class="text-center">Correct/Wrong</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Exam Date</th>
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
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
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
        // ১. রাউট সেটিংস
        const routes = {
            fetch: "{{ route('exam-package.leaderboard', $package->id) }}"
        };

        let currentPage = 1;

        // ২. ডাটা ফেচিং ফাংশন (AJAX)
        function fetchData(page = 1) {
            currentPage = page;
            $('#ajax-loader-leaderboard').css('display', 'flex');

            $.ajax({
                url: routes.fetch,
                type: "GET",
                data: {
                    page: page,
                    search: $('#searchInput').val()
                },
                success: function(res) {
                    renderTable(res.data, res.from, res.current_page);
                    renderPagination(res);
                    $('#paginationInfo').text(`Showing ${res.from || 0} to ${res.to || 0} of ${res.total} entries`);
                    $('#ajax-loader-leaderboard').hide();
                },
                error: function() {
                    $('#ajax-loader-leaderboard').hide();
                    alert('Failed to fetch data.');
                }
            });
        }

        // ৩. টেবিল রেন্ডারিং লজিক
        function renderTable(data, from, currentPage) {
            let html = '';
            if (data.length > 0) {
                data.forEach((item, index) => {
                    let rank = from + index;
                    let rankClass = rank === 1 ? 'rank-1' : (rank === 2 ? 'rank-2' : (rank === 3 ? 'rank-3' : ''));
                    let statusBadge = item.result_status === 'passed' ? 'bg-success' : 'bg-danger';

                    html += `
                    <tr>
                        <td><span class="badge rounded-pill ${rankClass} p-2 px-3 border">${rank}</span></td>
                        <td>
                            <div class="fw-bold">${item.user ? item.user.name : 'N/A'}</div>
                            <small class="text-muted"><i class="fa fa-phone me-1"></i>${item.user ? item.user.phone : 'N/A'}</small>
                        </td>
                        <td class="text-center fw-bold text-primary">
                            ${parseFloat(item.earned_marks).toFixed(2)} / ${parseFloat(item.total_marks).toFixed(2)}
                        </td>
                        <td class="text-center">
                            <span class="text-success fw-bold">${item.correct_answers}</span> / 
                            <span class="text-danger fw-bold">${item.wrong_answers}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge ${statusBadge}">${item.result_status.toUpperCase()}</span>
                        </td>
                        <td class="text-end small text-muted">
                            ${new Date(item.created_at).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                        </td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="6" class="text-center py-5 text-muted">No participants found for this exam.</td></tr>';
            }
            $('#tableBody').html(html);
        }

        // ৪. কাস্টম প্যাগিনেশন রেন্ডারার
        function renderPagination(data) {
            let html = '';
            if (data.last_page > 1) {
                // Previous Button
                html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="javascript:void(0)" data-page="${data.current_page - 1}">Prev</a>
                         </li>`;

                // Page Numbers
                for (let i = 1; i <= data.last_page; i++) {
                    if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                        html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                                    <a class="page-link" href="javascript:void(0)" data-page="${i}">${i}</a>
                                 </li>`;
                    } else if (i === data.current_page - 3 || i === data.current_page + 3) {
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

        // ৫. ইভেন্ট লিসেনার
        $('#searchInput').on('keyup', function() {
            fetchData(1);
        });

        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            let page = $(this).data('page');
            if (page) fetchData(page);
        });

        // ইনিশিয়াল ডাটা লোড
        fetchData();
    });
</script>
@endsection