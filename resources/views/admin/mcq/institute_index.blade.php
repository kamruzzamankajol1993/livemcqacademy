@extends('admin.master.master')

@section('title') Institute Wise Questions @endsection

@section('css')
<style>
    #ajax-loaderOne { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 50; display: none; align-items: center; justify-content: center; backdrop-filter: blur(1px); }
    .question-text { max-width: 350px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Institute Wise Question Bank</h2>
        </div>

        <div class="card shadow-sm border-0">
            <div id="ajax-loaderOne">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
            </div>
            <div class="card-body">
                <div class="row mb-4 g-3 bg-light p-3 rounded border">
                    <div class="col-md-3">
                        <label class="small fw-bold">Select Institute</label>
                        <select id="filter_institute" class="form-control select2">
                            <option value="">All Institutes</option>
                            @foreach($institutes as $ins) <option value="{{ $ins->id }}">{{ $ins->name_en }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">Class</label>
                        <select id="filter_class" class="form-control select2">
                            <option value="">All Classes</option>
                            @foreach($classes as $c) <option value="{{ $c->id }}">{{ $c->name_en }}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Subject</label>
                        <select id="filter_subject" class="form-control select2"><option value="">All Subjects</option></select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Search</label>
                        <input class="form-control" id="searchInput" type="search" placeholder="Search...">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button id="btnFilter" class="btn btn-primary w-100"><i class="fa fa-filter"></i></button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Sl</th>
                                <th>Question</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div id="pagination-info"></div>
                    <nav><ul class="pagination pagination-sm mb-0" id="pagination"></ul></nav>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var currentPage = 1;
        var routes = {
            fetch: "{{ route('institute.questions.ajax.data') }}",
            subjects: "{{ route('mcq.ajax.subjects') }}",
            show: "{{ route('mcq.show', ':id') }}",
            edit: "{{ route('mcq.edit', ':id') }}"
        };

        function fetchData() {
            $('#ajax-loaderOne').css('display', 'flex');
            $.get(routes.fetch, {
                page: currentPage,
                institute_id: $('#filter_institute').val(),
                class_id: $('#filter_class').val(),
                subject_id: $('#filter_subject').val(),
                search: $('#searchInput').val()
            }, function(res) {
                let rows = '';
                res.data.forEach((item, i) => {
                    let sl = (res.current_page - 1) * res.per_page + i + 1;
                    let qText = item.question ? item.question.replace(/<[^>]*>?/gm, '') : 'Image Based';
                    rows += `<tr>
                        <td>${sl}</td>
                        <td><div class="question-text" title="${qText}">${qText}</div></td>
                        <td>${item.class ? item.class.name_en : '--'}</td>
                        <td>${item.subject ? item.subject.name_en : '--'}</td>
                        <td>${item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}</td>
                        <td>
                            <a href="${routes.show.replace(':id', item.id)}" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i></a>
                            <a href="${routes.edit.replace(':id', item.id)}" class="btn btn-sm btn-outline-info"><i class="fa fa-edit"></i></a>
                        </td>
                    </tr>`;
                });
                $('#tableBody').html(rows || '<tr><td colspan="6" class="text-center">No questions found</td></tr>');
                renderPagination(res);
                $('#ajax-loaderOne').hide();
            });
        }

        $('#filter_class').on('change', function() {
            var clsId = $(this).val();
            if(clsId) {
                $.get(routes.subjects, { class_id: clsId }, function(res) {
                    var ops = '<option value="">All Subjects</option>';
                    res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                    $('#filter_subject').html(ops);
                });
            }
        });

        function renderPagination(data) {
            let html = '';
            if(data.last_page > 1) {
                html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}"><a class="page-link" data-page="${data.current_page - 1}">Prev</a></li>`;
                for(let i=1; i<=data.last_page; i++) {
                    html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" data-page="${i}">${i}</a></li>`;
                }
                html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}"><a class="page-link" data-page="${data.current_page + 1}">Next</a></li>`;
            }
            $('#pagination').html(html);
        }

        fetchData();
        $('#btnFilter').click(function() { currentPage = 1; fetchData(); });
        $(document).on('click', '.page-link', function(e) { e.preventDefault(); currentPage = $(this).data('page'); fetchData(); });
    });
</script>
@endsection