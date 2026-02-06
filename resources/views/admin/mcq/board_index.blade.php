@extends('admin.master.master')

@section('title') Board Question List | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<style>
    #ajax-loaderOne {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        z-index: 50;
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
        backdrop-filter: blur(1px);
    }
    .question-text { max-width: 350px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Board Question Bank</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Board Questions</li>
                </ol>
            </nav>
        </div>

        <div class="card shadow-sm border-0">
            <div id="ajax-loaderOne">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div class="card-body">
                @include('flash_message')

                {{-- Filters --}}
                <div class="row mb-4 g-3 bg-light p-3 rounded border">
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted">Filter by Board</label>
                        <select id="filter_board" class="form-control select2">
                            <option value="">All Boards</option>
                            @foreach($boards as $b) 
                                <option value="{{ $b->id }}">{{ $b->name_en }}</option> 
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold text-muted">Filter by Class</label>
                        <select id="filter_class" class="form-control select2">
                            <option value="">All Classes</option>
                            @foreach($classes as $c) 
                                <option value="{{ $c->id }}">{{ $c->name_en }}</option> 
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted">Filter by Subject</label>
                        <select id="filter_subject" class="form-control select2">
                            <option value="">All Subjects</option>
                            {{-- AJAX loaded based on class --}}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted">Search Question</label>
                        <input class="form-control" id="searchInput" type="search" placeholder="Type here...">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button id="btnFilter" class="btn btn-primary w-100"><i class="fa fa-filter"></i></button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="50">Sl</th>
                                <th>Question Statement</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Boards</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small" id="pagination-info"></div>
                    <nav><ul class="pagination pagination-sm justify-content-center mb-0" id="pagination"></ul></nav>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        var routes = {
            fetch: "{{ route('board.questions.ajax.data') }}",
            subjects: "{{ route('mcq.ajax.subjects') }}",
            edit: "{{ route('mcq.edit', ':id') }}",
            show: "{{ route('mcq.show', ':id') }}",
            destroy: "{{ route('mcq.destroy', ':id') }}",
        };

        var currentPage = 1;

        // --- Fetch Data AJAX ---
        function fetchData() {
            $('#ajax-loaderOne').css('display', 'flex');
            
            var params = {
                page: currentPage,
                search: $('#searchInput').val(),
                board_id: $('#filter_board').val(),
                class_id: $('#filter_class').val(),
                subject_id: $('#filter_subject').val()
            };

            $.get(routes.fetch, params, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let sl = (res.current_page - 1) * res.per_page + i + 1;
                        let qText = item.question ? item.question.replace(/<[^>]*>?/gm, '') : '<span class="text-danger">Image Based Question</span>';
                        let cls = item.class ? `<span class="badge bg-secondary">${item.class.name_en}</span>` : '--';
                        let sub = item.subject ? item.subject.name_en : '--';
                        
                        let editUrl = routes.edit.replace(':id', item.id);
                        let showUrl = routes.show.replace(':id', item.id);
                        let destroyUrl = routes.destroy.replace(':id', item.id);

                        rows += `<tr>
                            <td>${sl}</td>
                            <td><div class="question-text" title="${qText}">${qText}</div></td>
                            <td>${cls}</td>
                            <td>${sub}</td>
                            <td><span class="badge bg-primary">Board Question</span></td>
                            <td>
                                <a href="${showUrl}" class="btn btn-sm btn-outline-primary" title="View"><i class="fa fa-eye"></i></a>
                                <a href="${editUrl}" class="btn btn-sm btn-outline-info" title="Edit"><i class="fa fa-edit"></i></a>
                                <form action="${destroyUrl}" method="POST" class="d-inline delete-form">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" title="Delete"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>`;
                    });
                } else { 
                    rows = '<tr><td colspan="6" class="text-center text-muted">No Board Questions found for current filters.</td></tr>'; 
                }
                $('#tableBody').html(rows);
                renderPagination(res);
                $('#ajax-loaderOne').hide();
            }).fail(function() {
                $('#ajax-loaderOne').hide();
                alert('Error loading data!');
            });
        }

        // --- Dependent Dropdown: Class -> Subject ---
        $('#filter_class').on('change', function() {
            var clsId = $(this).val();
            $('#filter_subject').html('<option value="">Loading...</option>');
            if(clsId) {
                $.get(routes.subjects, { class_id: clsId }, function(res) {
                    var ops = '<option value="">All Subjects</option>';
                    res.forEach(el => ops += `<option value="${el.id}">${el.name_en}</option>`);
                    $('#filter_subject').html(ops);
                });
            } else {
                $('#filter_subject').html('<option value="">All Subjects</option>');
            }
        });

        // --- Pagination Rendering ---
        function renderPagination(data) {
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

        // --- Event Listeners ---
        fetchData();
        $('#btnFilter').click(function() { currentPage = 1; fetchData(); });
        $('#searchInput').on('keyup', function() { currentPage = 1; fetchData(); });
        $(document).on('click', '.page-link', function(e) { e.preventDefault(); currentPage = $(this).data('page'); fetchData(); });
        
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault(); 
            const form = $(this).closest('form');
            if(confirm('Are you sure you want to delete this board question?')) {
                form.submit();
            }
        });
    });
</script>
@endsection