@extends('admin.master.master')
@section('title', 'Student Performance Report')

@section('css')
<style>
    .report-loader {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.7); z-index: 10; display: none;
        align-items: center; justify-content: center;
    }
    .card-header { font-weight: bold; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <h4><i class="fa fa-chart-line me-2 text-primary"></i>Performance Report: {{ $student->name }}</h4>
            <a href="{{ route('student.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Back to Students
            </a>
        </div>

        {{-- ১. এক্সাম রেজাল্ট সেকশন (Exam Module) --}}
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-primary text-white">
                <i class="fa fa-graduation-cap me-1"></i> Exam Results
            </div>
            <div class="card-body position-relative">
                <div id="examLoader" class="report-loader"><div class="spinner-border text-primary"></div></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th>Exam Name</th>
                                <th>Score (Earned/Total)</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="examTableBody"></tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="small text-muted" id="exam-pagination-info"></div>
                    <ul class="pagination pagination-sm mb-0" id="exam-pagination"></ul>
                </div>
            </div>
        </div>

        {{-- ২. সেলফ টেস্ট সেকশন (Assessment Module) --}}
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-dark text-white">
                <i class="fa fa-user-edit me-1"></i> Self Test (Assessment) Results
            </div>
            <div class="card-body position-relative">
                <div id="selfLoader" class="report-loader"><div class="spinner-border text-dark"></div></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th>Test Date</th>
                                <th>Subject/Topic</th>
                                <th>Score</th>
                                <th>Correct/Wrong</th>
                            </tr>
                        </thead>
                        <tbody id="selfTableBody"></tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="small text-muted" id="self-pagination-info"></div>
                    <ul class="pagination pagination-sm mb-0" id="self-pagination"></ul>
                </div>
            </div>
        </div>

        {{-- ৩. কেনা বইয়ের তালিকা (Purchased Books) --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white">
                <i class="fa fa-book me-1"></i> Purchased Books
            </div>
            <div class="card-body position-relative">
                <div id="bookLoader" class="report-loader"><div class="spinner-border text-success"></div></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th>Book Info</th>
                                <th>Price</th>
                                <th>TrxID</th>
                                <th>Purchase Date</th>
                            </tr>
                        </thead>
                        <tbody id="bookTableBody"></tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="small text-muted" id="book-pagination-info"></div>
                    <ul class="pagination pagination-sm mb-0" id="book-pagination"></ul>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // ১. এক্সাম ডাটা ফেচ
    function fetchExamData(page = 1) {
        $('#examLoader').css('display', 'flex');
        $.get("{{ route('students.report', $student->id) }}", { type: 'exam', page: page }, function(res) {
            let rows = '';
            if(res.data.length > 0) {
                res.data.forEach(item => {
                    let leaderboardUrl = "{{ route('exam-package.leaderboard', ':id') }}".replace(':id', item.exam_package_id);
                    rows += `<tr>
                        <td>${item.exam_package ? item.exam_package.exam_name : 'N/A'}</td>
                        <td><b>${item.earned_marks}</b> / ${item.total_marks}</td>
                        <td><span class="badge bg-${item.result_status == 'passed' ? 'success' : 'danger'}">${item.result_status}</span></td>
                        <td class="text-center"><a href="${leaderboardUrl}" class="btn btn-xs btn-info py-0">Leaderboard</a></td>
                    </tr>`;
                });
            } else { rows = '<tr><td colspan="4" class="text-center">No exam results found.</td></tr>'; }
            $('#examTableBody').html(rows);
            renderPagination(res, 'exam');
            $('#examLoader').hide();
        });
    }

    // ২. সেলফ টেস্ট ডাটা ফেচ
    function fetchSelfData(page = 1) {
        $('#selfLoader').css('display', 'flex');
        $.get("{{ route('students.report', $student->id) }}", { type: 'self', page: page }, function(res) {
            let rows = '';
            if(res.data.length > 0) {
                res.data.forEach(item => {
                    rows += `<tr>
                        <td>${new Date(item.created_at).toLocaleDateString()}</td>
                        <td>Subject ID: ${item.subject_id || 'N/A'}</td>
                        <td><b>${item.earned_marks}</b> / ${item.total_marks}</td>
                        <td><span class="text-success">${item.correct_answers}</span> / <span class="text-danger">${item.wrong_answers}</span></td>
                    </tr>`;
                });
            } else { rows = '<tr><td colspan="4" class="text-center">No self tests taken.</td></tr>'; }
            $('#selfTableBody').html(rows);
            renderPagination(res, 'self');
            $('#selfLoader').hide();
        });
    }

    // ৩. পারচেজড বুক ডাটা ফেচ
    function fetchBookData(page = 1) {
        $('#bookLoader').css('display', 'flex');
        $.get("{{ route('students.report', $student->id) }}", { type: 'book', page: page }, function(res) {
            let rows = '';
            if(res.data.length > 0) {
                res.data.forEach(item => {
                    let bookImg = item.book_image ? `{{ asset('public') }}/${item.book_image}` : `{{ asset('public/assets/images/no-image.png') }}`;
                    rows += `<tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${bookImg}" width="35" class="me-2 rounded border">
                                <div class="fw-bold small">${item.book_title}</div>
                            </div>
                        </td>
                        <td>${item.amount} TK</td>
                        <td class="small text-muted">${item.transaction_id || 'N/A'}</td>
                        <td>${new Date(item.created_at).toLocaleDateString()}</td>
                    </tr>`;
                });
            } else { rows = '<tr><td colspan="4" class="text-center">No books purchased yet.</td></tr>'; }
            $('#bookTableBody').html(rows);
            renderPagination(res, 'book');
            $('#bookLoader').hide();
        });
    }

    // কাস্টম প্যাগিনেশন রেন্ডারার
    function renderPagination(data, type) {
        $(`#${type}-pagination-info`).html(`Showing ${data.from || 0} to ${data.to || 0} of ${data.total} entries`);
        let html = '';
        if(data.last_page > 1) {
            let funcName = type === 'exam' ? 'fetchExamData' : (type === 'self' ? 'fetchSelfData' : 'fetchBookData');
            
            html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="${funcName}(${data.current_page - 1})">Prev</a></li>`;
            
            for(let i=1; i<=data.last_page; i++) {
                if(i==1 || i==data.last_page || (i>=data.current_page-1 && i<=data.current_page+1)) {
                    html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="${funcName}(${i})">${i}</a></li>`;
                }
            }
            
            html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="${funcName}(${data.current_page + 1})">Next</a></li>`;
        }
        $(`#${type}-pagination`).html(html);
    }

    // ইনিশিয়াল কল
    fetchExamData();
    fetchSelfData();
    fetchBookData();
});
</script>
@endsection