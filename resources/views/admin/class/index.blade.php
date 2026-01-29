@extends('admin.master.master')

@section('title')
Class Management | {{ $ins_name ?? 'App' }}
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
    /* Custom Styles */
    .color-preview-box { width: 100%; height: 40px; border-radius: 5px; border: 1px solid #ddd; margin-top: 5px; background-color: #ddd; }
    .table-color-preview { width: 25px; height: 25px; border-radius: 50%; border: 1px solid #ccc; display: inline-block; vertical-align: middle; }
    
    /* Sortable List Styles */
    #sortable-list { list-style-type: none; margin: 0; padding: 0; }
    #sortable-list li { 
        margin: 5px 0; padding: 10px 15px; background: #fff; 
        border: 1px solid #ddd; border-radius: 4px; cursor: grab; 
        display: flex; align-items: center; justify-content: space-between; 
    }
    #sortable-list li:hover { background-color: #f8f9fa; }
    
    /* Pagination Styles */
    .page-link { cursor: pointer; }
    .select2-container { z-index: 99999; } /* Modal Fix */
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h2 class="mb-0">Class List</h2>
            <div class="d-flex gap-2">
                <button type="button" data-bs-toggle="modal" data-bs-target="#importModal" class="btn btn-success text-white">
                    <i class="fa fa-file-excel me-1"></i> Import Excel
                </button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#addModal" class="btn btn-primary text-white">
                    <i class="fa fa-plus me-1"></i> Add New Class
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white border-bottom-0">
                <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#listView" type="button" role="tab">
                            <i class="fa fa-list me-1"></i> List View
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sort-tab" data-bs-toggle="tab" data-bs-target="#sortView" type="button" role="tab">
                            <i class="fa fa-sort me-1"></i> Drag & Drop Sort
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                @include('flash_message')
                
                <div class="tab-content" id="classTabContent">
                    
                    <div class="tab-pane fade show active" id="listView" role="tabpanel">
                         <div class="d-flex justify-content-end mb-3">
                            <input class="form-control" id="searchInput" type="search" placeholder="Search class..." style="max-width: 250px;">
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Sl</th>
                                        <th>Image</th>
                                        <th class="sortable" data-column="name_en">Name (EN)</th>
                                        <th class="sortable" data-column="name_bn">Name (BN)</th>
                                        <th>Categories</th>
                                        <th>Color</th>
                                        <th class="sortable" data-column="status">Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 border-top pt-3">
                            <div class="text-muted" id="pagination-info"></div>
                            <nav>
                                <ul class="pagination justify-content-center mb-0" id="pagination"></ul>
                            </nav>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="sortView" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle me-1"></i> Drag items to reorder. Changes save automatically.
                        </div>
                        <ul id="sortable-list">
                            @foreach($classes as $item)
                            <li data-id="{{ $item->id }}">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fa fa-bars text-muted"></i>
                                    @if($item->image)
                                        <img src="{{ asset($item->image) }}" width="40" class="rounded">
                                    @else
                                        <span class="badge bg-secondary">No Img</span>
                                    @endif
                                    <div>
                                        <strong>{{ $item->name_en }}</strong> <br>
                                        <small class="text-muted">{{ $item->name_bn }}</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @foreach($item->categories as $cat)
                                        <span class="badge bg-light text-dark border">{{ $cat->english_name }}</span>
                                    @endforeach
                                    @if($item->color)
                                        <div class="table-color-preview" style="background: {{ $item->color }};" title="{{ $item->color }}"></div>
                                    @endif
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('class.import') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Import Classes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4 text-center">
                    <p class="text-muted mb-2">Please download the sample file to verify the format.</p>
                    <a href="{{ route('class.sample') }}" class="btn btn-info text-white btn-sm">
                        <i class="fa fa-download me-1"></i> Download Sample File
                    </a>
                </div>
                
                <hr>

                <div class="mb-3 mt-3">
                    <label class="form-label">Upload Excel File</label>
                    <input type="file" name="file" class="form-control" required accept=".xlsx,.xls,.csv">
                    <small class="text-muted d-block mt-1">Allowed columns: <strong>name_en, name_bn, color, status</strong></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Import</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('schoolClass.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add New Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name (English) <span class="text-danger">*</span></label>
                        <input type="text" name="name_en" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name (Bangla) <span class="text-danger">*</span></label>
                        <input type="text" name="name_bn" class="form-control" required>
                    </div>
                    
                    {{-- Category Select2 --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Assign Categories <span class="text-danger">*</span></label>
                        <select name="category_ids[]" class="form-control select2-modal" multiple required style="width: 100%">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->english_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Color --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Color (Hex)</label>
                        <div class="input-group">
                            <input type="text" name="color" id="add_color_input" class="form-control">
                            <input type="color" class="form-control" style="max-width: 50px;" onchange="updateColor(this, 'add_color_input', 'add_prev')">
                        </div>
                        <div id="add_prev" class="color-preview-box"></div>
                    </div>

                    {{-- Image --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" onchange="previewImg(this, 'add_img_prev')">
                        <div class="mt-2">
                            <img id="add_img_prev" src="#" style="display:none; width:60px; height:60px;" class="img-thumbnail">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Class</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="editForm" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name (English) <span class="text-danger">*</span></label>
                        <input type="text" name="name_en" id="edit_name_en" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name (Bangla) <span class="text-danger">*</span></label>
                        <input type="text" name="name_bn" id="edit_name_bn" class="form-control" required>
                    </div>
                    
                    {{-- Category Select2 --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Assign Categories</label>
                        <select name="category_ids[]" id="edit_categories" class="form-control select2-modal" multiple required style="width: 100%">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->english_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Color --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Color</label>
                        <div class="input-group">
                            <input type="text" name="color" id="edit_color" class="form-control">
                            <input type="color" class="form-control" style="max-width: 50px;" onchange="updateColor(this, 'edit_color', 'edit_prev')">
                        </div>
                        <div id="edit_prev" class="color-preview-box"></div>
                    </div>

                    {{-- Image --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" onchange="previewImg(this, 'edit_img_prev')">
                        <div class="mt-2">
                            <img id="edit_img_prev" src="#" style="display:none; width:60px; height:60px;" class="img-thumbnail">
                        </div>
                    </div>

                     <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update Class</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script>
    // --- Helper Functions ---
    function previewImg(input, id) {
        if(input.files && input.files[0]){ 
            var r = new FileReader(); 
            r.onload = function(e){ $('#'+id).attr('src', e.target.result).show(); }; 
            r.readAsDataURL(input.files[0]); 
        }
    }
    function updateColor(p, i, d) { $('#'+i).val(p.value); $('#'+d).css('background', p.value); }

    $(document).ready(function() {
        // Init Select2
        $('.select2-modal').select2({ dropdownParent: $('#addModal') });
        $('#editModal .select2-modal').select2({ dropdownParent: $('#editModal') });

        // --- DEFINED ROUTES ---
        var routes = {
            fetch: "{{ route('ajax.class.data') }}",
            store: "{{ route('schoolClass.store') }}",
            show: "{{ route('schoolClass.show', ':id') }}",
            update: "{{ route('schoolClass.update', ':id') }}",
            destroy: "{{ route('schoolClass.destroy', ':id') }}",
            reorder: "{{ route('class.reorder') }}",
            csrf: "{{ csrf_token() }}"
        };

        var currentPage = 1, searchTerm = '', sortColumn = 'serial', sortDirection = 'asc';

        // --- FETCH DATA FUNCTION ---
        function fetchData() {
            $.get(routes.fetch, { 
                page: currentPage, 
                search: searchTerm, 
                sort: sortColumn, 
                direction: sortDirection 
            }, function(res) {
                // 1. Render Table
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        let serial = (res.current_page - 1) * res.per_page + i + 1;
                        let cats = item.categories.map(c => `<span class="badge bg-secondary">${c.english_name}</span>`).join(' ');
                        let img = item.image ? `{{ asset('') }}public/${item.image}` : 'https://placehold.co/40x40/EFEFEF/AAAAAA&text=No+Img';
                        let color = item.color ? `<div class="table-color-preview" style="background:${item.color}"></div>` : '--';
                        let status = item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';

                        // Route Generation in JS
                        let destroyUrl = routes.destroy.replace(':id', item.id);

                        rows += `<tr>
                            <td>${serial}</td>
                            <td><img src="${img}" width="40" class="img-thumbnail"></td>
                            <td>${item.name_en}</td>
                            <td>${item.name_bn}</td>
                            <td>${cats}</td>
                            <td>${color}</td>
                            <td>${status}</td>
                            <td>
                                <button class="btn btn-sm btn-info btn-edit text-white" data-id="${item.id}"><i class="fa fa-edit"></i></button>
                                <form action="${destroyUrl}" method="POST" class="d-inline delete-form">
                                    <input type="hidden" name="_token" value="${routes.csrf}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="8" class="text-center text-muted">No classes found</td></tr>';
                }
                $('#tableBody').html(rows);

                // 2. Render Custom Pagination
                renderPagination(res);
            });
        }

        // --- CUSTOM PAGINATION RENDERER ---
        function renderPagination(data) {
            let paginationHtml = '';
            
            if(data.total > 0) {
                $('#pagination-info').html(`Showing ${data.from} to ${data.to} of ${data.total} entries`);
            } else {
                $('#pagination-info').html('');
            }

            if (data.last_page > 1) {
                paginationHtml += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${data.current_page - 1}">Prev</a></li>`;

                for (let i = 1; i <= data.last_page; i++) {
                    if (i == 1 || i == data.last_page || (i >= data.current_page - 1 && i <= data.current_page + 1)) {
                         paginationHtml += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                    } else if (i == data.current_page - 2 || i == data.current_page + 2) {
                        paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                }

                paginationHtml += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${data.current_page + 1}">Next</a></li>`;
            }
            $('#pagination').html(paginationHtml);
        }

        // --- EVENTS ---
        
        fetchData();

        $('#searchInput').on('keyup', function() {
            searchTerm = $(this).val(); currentPage = 1; fetchData();
        });

        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            let page = $(this).data('page');
            if(page) { currentPage = page; fetchData(); }
        });

        // Edit Button (Using Routes)
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var showUrl = routes.show.replace(':id', id);
            var updateUrl = routes.update.replace(':id', id);

            $.get(showUrl, function(data) {
                $('#edit_name_en').val(data.name_en);
                $('#edit_name_bn').val(data.name_bn);
                $('#edit_color').val(data.color);
                $('#edit_prev').css('background', data.color);
                $('#edit_status').val(data.status);
                
                var catIds = data.categories.map(c => c.id);
                $('#edit_categories').val(catIds).trigger('change');

                if(data.image) {
                    $('#edit_img_prev').attr('src', `{{ asset('') }}public/${data.image}`).show();
                } else {
                    $('#edit_img_prev').hide();
                }
                
                $('#editForm').attr('action', updateUrl);
                $('#editModal').modal('show');
            });
        });

        $(document).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            const form = $(this).closest('form');
            Swal.fire({ 
                title: 'Are you sure?', 
                text: "You won't be able to revert this!",
                icon: 'warning', 
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!' 
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });

        $("#sortable-list").sortable({
            placeholder: "ui-state-highlight",
            update: function(event, ui) {
                var order = [];
                $('#sortable-list li').each(function(index) { 
                    order.push({ id: $(this).data('id'), position: index + 1 }); 
                });
                $.post(routes.reorder, { order: order, _token: routes.csrf }, function() {
                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                    Toast.fire({ icon: 'success', title: 'Order updated' });
                    fetchData();
                });
            }
        });

        $('#addModal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $(this).find('.select2-modal').val('').trigger('change');
            $('#add_img_prev').hide().attr('src', '#');
            $('#add_prev').css('background', '');
        });
    });
</script>
@endsection