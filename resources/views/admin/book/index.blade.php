@extends('admin.master.master')
@section('title', 'Book List')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .filter-card { background: #f8f9fa; border: 1px solid #e3e6f0; }
    .table img { width: 40px; height: 50px; object-fit: cover; border-radius: 4px; }
    .badge-paid { background-color: #6f42c1; color: white; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <h4 class="mb-0">Book Management</h4>
            <a href="{{ route('book.create') }}" class="btn btn-primary">
                <i class="fa fa-plus-circle me-1"></i> Add New Book
            </a>
        </div>

        {{-- ফিল্টার সেকশন --}}
        <div class="card filter-card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="small fw-bold">Category</label>
                        <select id="filter_category" class="form-control select2">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Class</label>
                        <select id="filter_class" class="form-control select2">
                            <option value="">All Classes</option>
                            @foreach($classes as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Subject (Dependent)</label>
                        <select id="filter_subject" class="form-control select2">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Search</label>
                        <input type="text" id="searchInput" class="form-control" placeholder="Title, ISBN, Author...">
                    </div>
                </div>
            </div>
        </div>

        {{-- ডাটা টেবিল --}}
        <div class="card shadow-sm border-0">
            <div class="card-body">
                @include('flash_message')
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>SL</th>
                                <th>Cover</th>
                                <th>Book Info</th>
                                <th>Categorization</th>
                                <th>Type/Price</th>
                                <th>Downloads</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            {{-- AJAX ডাটা এখানে লোড হবে --}}
                        </tbody>
                    </table>
                </div>

                {{-- প্যাগিনেশন --}}
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div id="paginationInfo" class="small text-muted"></div>
                    <ul class="pagination pagination-sm mb-0" id="customPagination"></ul>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        const routes = {
            fetch: "{{ route('book.fetch') }}",
            getSubjects: "{{ route('book.getSubjects') }}",
            show: "{{ route('book.show', ':id') }}",
            edit: "{{ route('book.edit', ':id') }}",
            delete: "{{ route('book.destroy', ':id') }}"
        };

        // ১. ডাটা ফেচ ফাংশন
        function fetchBooks(page = 1) {
            let params = {
                page: page,
                search: $('#searchInput').val(),
                category_id: $('#filter_category').val(),
                class_id: $('#filter_class').val(),
                subject_id: $('#filter_subject').val()
            };

            $.get(routes.fetch, params, function(res) {
                renderTable(res);
                renderPagination(res);
            });
        }

        // ২. টেবিল রেন্ডারিং
        function renderTable(res) {
            let rows = '';
            if (res.data.length > 0) {
                res.data.forEach((item, index) => {
                    let sl = res.from + index;
                    let img = item.image ? `{{ asset('') }}public/${item.image}` : 'https://via.placeholder.com/40x50?text=No+Img';
                    let classes = item.school_classes.map(c => `<span class="badge bg-light text-dark border">${c.name_en}</span>`).join(' ');
                    let typeBadge = item.type === 'free' ? 'bg-success' : 'badge-paid';
                    
                    rows += `<tr>
                        <td>${sl}</td>
                        <td><img src="${img}" class="shadow-sm"></td>
                        <td>
                            <div class="fw-bold">${item.title}</div>
                            <small class="text-muted">ISBN: ${item.isbn_code || 'N/A'} | Ed: ${item.edition || 'N/A'}</small>
                        </td>
                        <td>
                            <div class="small"><i class="fa fa-folder me-1"></i>${item.category.name_en}</div>
                            <div class="mt-1">${item.subject ? `<i class="fa fa-book me-1"></i>${item.subject.name_en}` : ''}</div>
                            <div class="mt-1">${classes}</div>
                        </td>
                        <td>
                            <span class="badge ${typeBadge}">${item.type.toUpperCase()}</span>
                            ${item.type === 'paid' ? `<div class="small mt-1 fw-bold">${item.price} TK</div>` : ''}
                        </td>
                        <td class="text-center text-muted"><i class="fa fa-download me-1"></i>${item.total_download}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                <a href="${routes.show.replace(':id', item.id)}" class="btn btn-sm btn-warning text-white"><i class="fa fa-eye"></i></a>
                                <a href="${routes.edit.replace(':id', item.id)}" class="btn btn-sm btn-info text-white"><i class="fa fa-edit"></i></a>
                                <form action="${routes.delete.replace(':id', item.id)}" method="POST" class="delete-form">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger confirm-delete"><i class="fa fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>`;
                });
            } else {
                rows = '<tr><td colspan="7" class="text-center py-4 text-muted">No books found matching your criteria.</td></tr>';
            }
            $('#tableBody').html(rows);
            $('#paginationInfo').text(`Showing ${res.from || 0} to ${res.to || 0} of ${res.total} entries`);
        }

        // ৩. প্যাগিনেশন রেন্ডারিং
        function renderPagination(res) {
            let pagination = '';
            if (res.last_page > 1) {
                pagination += `<li class="page-item ${res.current_page == 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${res.current_page - 1}">Prev</a></li>`;
                for (let i = 1; i <= res.last_page; i++) {
                    pagination += `<li class="page-item ${res.current_page == i ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }
                pagination += `<li class="page-item ${res.current_page == res.last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${res.current_page + 1}">Next</a></li>`;
            }
            $('#customPagination').html(pagination);
        }

        // ৪. ইভেন্ট হ্যান্ডলার (Search & Filters)
        $('#searchInput').on('keyup', function() { fetchBooks(1); });
        $('#filter_category, #filter_subject').on('change', function() { fetchBooks(1); });

        // ৫. Dependent Filter Logic (Class -> Subject)
        $('#filter_class').on('change', function() {
            let classId = $(this).val();
            let subjectDropdown = $('#filter_subject');

            if (classId) {
                $.get(routes.getSubjects, { class_ids: [classId] }, function(res) {
                    let options = '<option value="">All Subjects</option>';
                    res.forEach(sub => { options += `<option value="${sub.id}">${sub.name_en}</option>`; });
                    subjectDropdown.html(options);
                    fetchBooks(1);
                });
            } else {
                // ক্লাস রিসেট হলে সব সাবজেক্ট ফিরিয়ে আনা
                location.reload(); 
            }
        });

        // ৬. প্যাগিনেশন ক্লিক
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            fetchBooks($(this).data('page'));
        });

        // ৭. ডিলিট কনফার্মেশন
        $(document).on('click', '.confirm-delete', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "This book will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });

        // ইনিশিয়াল লোড
        fetchBooks();
    });
</script>
@endsection