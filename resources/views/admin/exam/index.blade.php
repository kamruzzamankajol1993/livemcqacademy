@extends('admin.master.master')

@section('title') Exam Setup | {{ $ins_name ?? 'App' }} @endsection

@section('css')
{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    #ajax-loaderOne {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.8); z-index: 2000; display: none;
        align-items: center; justify-content: center; border-radius: 0.375rem;
        backdrop-filter: blur(1px);
    }
    /* Select2 Modal Fix */
    .select2-container--open { z-index: 9999999 !important; }
    .question-text { max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Exam Setup & Configuration</h2>
            <button class="btn btn-primary" id="btnCreate">
                <i class="fa fa-plus me-1"></i> Configure New Exam
            </button>
        </div>

        <div class="card shadow-sm border-0 position-relative">
            {{-- Loader --}}
            <div id="ajax-loaderOne">
                <div class="spinner-border text-primary" role="status"></div>
            </div>

            <div class="card-body">
                @include('flash_message')

                <div class="row mb-3 justify-content-end">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by Question count...">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="50">Sl</th>
                                <th>Exam Categories</th>
                                <th>Total Qus.</th>
                                <th>Marks/Qus.</th>
                                <th>Neg. Marks</th>
                                <th>Pass Mark</th>
                                <th>Duration</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            {{-- AJAX Data --}}
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small" id="pagination-info"></div>
                    <nav><ul class="pagination pagination-sm mb-0" id="pagination"></ul></nav>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- Global Modal Container for Create/Edit --}}
<div class="modal fade" id="examModal" tabindex="-1" aria-labelledby="examModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div id="modalContent">
            {{-- AJAX will load create.blade.php or edit.blade.php content here --}}
        </div>
    </div>
</div>
@endsection

@section('script')
{{-- SweetAlert2 & Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        let currentPage = 1;
        const routes = {
            fetch: "{{ route('exam-setup.index') }}",
            create: "{{ route('exam-setup.create') }}",
            edit: "{{ route('exam-setup.edit', ':id') }}",
            destroy: "{{ route('exam-setup.destroy', ':id') }}"
        };

        // --- 1. Fetch Table Data ---
        function fetchData() {
            $('#ajax-loaderOne').css('display', 'flex');
            let search = $('#searchInput').val();

            $.get(routes.fetch, { page: currentPage, search: search }, function(res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let sl = (res.current_page - 1) * res.per_page + i + 1;
                        let categories = item.category_names ? item.category_names.join(', ') : '--';
                        let negativeMarks = item.negative_marks ? item.negative_marks.join(', ') : '0';

                        rows += `<tr>
                            <td>${sl}</td>
                            <td class="question-text" title="${categories}">${categories}</td>
                            <td>${item.total_questions}</td>
                            <td>${item.per_question_mark}</td>
                            <td class="text-danger fw-bold">${negativeMarks}</td>
                            <td>${item.pass_mark}</td>
                            <td>${item.exam_duration_minutes} Min</td>
                            <td>
                                <button class="btn btn-sm btn-info text-white btn-edit" data-id="${item.id}" title="Edit"><i class="fa fa-edit"></i></button>
                                <form action="${routes.destroy.replace(':id', item.id)}" method="POST" class="d-inline delete-form">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger btn-delete" title="Delete"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="8" class="text-center text-muted">No exam configurations found.</td></tr>';
                }
                $('#tableBody').html(rows);
                renderPagination(res);
                $('#ajax-loaderOne').hide();
            }).fail(function() {
                $('#ajax-loaderOne').hide();
                toastr.error('Failed to load data.');
            });
        }

        // --- 2. Pagination Rendering ---
        function renderPagination(data) {
            let html = '';
            if(data.total > 0) $('#pagination-info').html(`Showing ${data.from} to ${data.to} of ${data.total} configurations`);
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

        // --- 3. Modal & Select2 Logic ---
        // Create
        $('#btnCreate').click(function() {
            $('#ajax-loaderOne').show();
            $.get(routes.create, function(html) {
                $('#modalContent').html(html);
                $('#examModal').modal('show');
                $('#ajax-loaderOne').hide();
            });
        });

        // Edit
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            $('#ajax-loaderOne').show();
            $.get(routes.edit.replace(':id', id), function(html) {
                $('#modalContent').html(html);
                $('#examModal').modal('show');
                $('#ajax-loaderOne').hide();
            });
        });

        // Select2 Fix for Bootstrap Modal
        $('#examModal').on('shown.bs.modal', function () {
            $('.select2').select2({
                dropdownParent: $('#examModal'),
                width: '100%',
                placeholder: "-- Select Multiple --"
            });
        });

        // --- 4. SweetAlert2 Delete ---
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "This configuration will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // --- 5. Initial Load & Events ---
        fetchData();
        $('#searchInput').on('keyup', function() { currentPage = 1; fetchData(); });
        $(document).on('click', '.page-link', function(e) { 
            e.preventDefault(); 
            currentPage = $(this).data('page'); 
            fetchData(); 
        });
    });
</script>
@endsection