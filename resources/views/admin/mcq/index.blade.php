@extends('admin.master.master')

@section('title') MCQ List | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<style>
    #ajax-loaderOne {
        position: absolute; /* Changed from fixed to absolute */
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8); /* Slightly more opaque */
        z-index: 50; /* Z-index lowered as it's inside card */
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem; /* Matches Card Border Radius */
        backdrop-filter: blur(1px);
    }
    .question-text { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
@endsection

@section('body')
<div id="ajax-loaderOne">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">MCQ Question Bank</h2>
            <div class="d-flex gap-2">
                <button data-bs-toggle="modal" data-bs-target="#importModal" class="btn btn-success text-white"><i class="fa fa-file-excel me-1"></i> Import</button>
                <a href="{{ route('mcq.create') }}" class="btn btn-primary text-white"><i class="fa fa-plus me-1"></i> Add New</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @include('flash_message')

                {{-- Filters --}}
                <div class="row mb-3 g-2">
                    <div class="col-md-3">
                        <select id="filter_class" class="form-control">
                            <option value="">All Classes</option>
                            @foreach($classes as $c) <option value="{{ $c->id }}">{{ $c->name_en }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="filter_subject" class="form-control">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $s) <option value="{{ $s->id }}">{{ $s->name_en }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input class="form-control" id="searchInput" type="search" placeholder="Search Question...">
                    </div>
                    <div class="col-md-2">
                        <button id="btnFilter" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Question</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Chapter</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted" id="pagination-info"></div>
                    <nav><ul class="pagination justify-content-center mb-0" id="pagination"></ul></nav>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- Import Modal --}}
{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('mcq.import') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Import MCQs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Download Sample Button --}}
                <div class="text-center mb-4">
                    <p class="text-muted mb-2">Please download the sample file to understand the format.</p>
                    <a href="{{ route('mcq.sample') }}" class="btn btn-info text-white">
                        <i class="fa fa-download me-1"></i> Download Sample Excel
                    </a>
                </div>

                {{-- File Upload Input --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Upload Excel File</label>
                    <input type="file" name="file" class="form-control" required accept=".xlsx,.xls,.csv">
                </div>

                {{-- Important Instructions --}}
                <div class="alert alert-warning border-warning">
                    <strong class="text-dark"><i class="fa fa-exclamation-triangle me-1"></i> Important Instructions:</strong>
                    <ul class="mb-0 mt-2 small text-dark ps-3">
                        <li>Do <strong>not</strong> use IDs. Use exact <strong>Names</strong> (e.g., 'Dhaka College', 'Class 10').</li>
                        <li>Ensure that <strong>Institute, Board, Year, Class, Subject, etc.</strong> already exist in the system.</li>
                        <li>If any name (e.g., 'Physics') is not found in the database, the import will <strong>fail</strong> with an error.</li>
                        <li>Required Columns: <code>question</code>, <code>class_name</code>, <code>subject_name</code>, <code>answer</code>, etc.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-upload me-1"></i> Upload & Import</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var routes = {
            fetch: "{{ route('mcq.ajax.data') }}", // রাউটটি web.php তে ডিফাইন করতে হবে
            edit: "{{ route('mcq.edit', ':id') }}",
            show: "{{ route('mcq.show', ':id') }}",
            destroy: "{{ route('mcq.destroy', ':id') }}",
        };

        var currentPage = 1;

        function fetchData() {
            $('#ajax-loaderOne').css('display', 'flex');
            var search = $('#searchInput').val();
            var class_id = $('#filter_class').val();
            var subject_id = $('#filter_subject').val();

            $.get(routes.fetch, { page: currentPage, search: search, class_id: class_id, subject_id: subject_id }, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let sl = (res.current_page - 1) * res.per_page + i + 1;
                        let qText = item.question.replace(/<[^>]*>?/gm, ''); // Strip HTML tags
                        let cls = item.class ? `<span class="badge bg-info text-dark">${item.class.name_en}</span>` : '--';
                        let sub = item.subject ? item.subject.name_en : '--';
                        let chap = item.chapter ? item.chapter.name_en : '--';
                        let status = item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                        
                        let editUrl = routes.edit.replace(':id', item.id);
                        let showUrl = routes.show.replace(':id', item.id);
                        let destroyUrl = routes.destroy.replace(':id', item.id);

                        rows += `<tr>
                            <td>${sl}</td>
                            <td><div class="question-text" title="${qText}">${qText}</div></td>
                            <td>${cls}</td>
                            <td>${sub}</td>
                            <td>${chap}</td>
                            <td>${status}</td>
                            <td>
                                <a href="${showUrl}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
                                <a href="${editUrl}" class="btn btn-sm btn-info text-white"><i class="fa fa-edit"></i></a>
                                <form action="${destroyUrl}" method="POST" class="d-inline delete-form">@csrf @method('DELETE')<button class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i></button></form>
                            </td>
                        </tr>`;
                    });
                } else { rows = '<tr><td colspan="7" class="text-center text-muted">No MCQs found</td></tr>'; }
                $('#tableBody').html(rows);
                renderPagination(res);
                $('#ajax-loaderOne').hide();
            }).fail(function() {
                // Hide Loader on Error
                $('#ajax-loaderOne').hide();
                alert('Something went wrong! Please try again.');
            });
        }

        // Pagination & Filter Events
        function renderPagination(data) { /* Same pagination logic as previous modules */ 
             let html = '';
            if(data.total > 0) $('#pagination-info').html(`Showing ${data.from} to ${data.to} of ${data.total} entries`);
            else $('#pagination-info').html('');

            if(data.last_page > 1) {
                html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}"><a class="page-link" data-page="${data.current_page - 1}">Prev</a></li>`;
                for(let i=1; i<=data.last_page; i++) {
                    if(i==1 || i==data.last_page || (i>=data.current_page-1 && i<=data.current_page+1)) {
                        html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" data-page="${i}">${i}</a></li>`;
                    }
                }
                html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}"><a class="page-link" data-page="${data.current_page + 1}">Next</a></li>`;
            }
            $('#pagination').html(html);
        }

        fetchData();
        $('#btnFilter').click(function() { currentPage = 1; fetchData(); });
        $('#searchInput').on('keyup', function() { currentPage = 1; fetchData(); });
        $(document).on('click', '.page-link', function(e) { e.preventDefault(); currentPage = $(this).data('page'); fetchData(); });
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault(); const form = $(this).closest('form');
            Swal.fire({ title: 'Are you sure?', icon: 'warning', showCancelButton: true }).then((r) => { if(r.isConfirmed) form.submit(); });
        });
    });
</script>
@endsection