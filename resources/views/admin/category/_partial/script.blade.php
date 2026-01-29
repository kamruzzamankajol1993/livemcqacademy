
{{-- Select2 & jQuery UI Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script>
    // --- HELPER FUNCTIONS ---

    // 1. Image Preview Function
    function previewImage(input, previewId) {
        var preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // 2. Color Picker Helper
    function updateColorInput(picker, inputId, previewId) {
        document.getElementById(inputId).value = picker.value;
        document.getElementById(previewId).style.background = picker.value;
    }

    $(document).ready(function() {
        // --- INITIALIZATION ---

        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
        var currentPage = 1, searchTerm = '', sortColumn = 'serial', sortDirection = 'asc';

        // Initialize Select2 for Add Modal
        $('.select2-modal').select2({
            dropdownParent: $('#addModal')
        });

        // Initialize Select2 for Edit Modal
        $('#editModal .select2-modal').select2({
            dropdownParent: $('#editModal')
        });

        // --- DRAG & DROP SORTING LOGIC ---
        $("#sortable-list").sortable({
            placeholder: "ui-state-highlight",
            update: function(event, ui) {
                var order = [];
                // Get new order
                $('#sortable-list li').each(function(index) {
                    order.push({
                        id: $(this).data('id'),
                        position: index + 1
                    });
                });

                // Send to Server via AJAX
                $.post("{{ route('category.reorder') }}", {
                    order: order,
                    _token: '{{ csrf_token() }}'
                }, function(response) {
                    // Success Notification
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Order updated successfully'
                    });

                    // Refresh Table View to sync serials
                    fetchData();
                });
            }
        });

        // --- DATA TABLE LOGIC ---

        var routes = {
            fetch: "{{ route('ajax.category.data') }}",
            show: id => `{{ route('category.show', ':id') }}`.replace(':id', id),
            update: id => `{{ route('category.update', ':id') }}`.replace(':id', id),
            delete: id => `{{ route('category.destroy', ':id') }}`.replace(':id', id),
            csrf: "{{ csrf_token() }}"
        };

        function fetchData() {
            $.get(routes.fetch, {
                page: currentPage,
                search: searchTerm,
                sort: sortColumn,
                direction: sortDirection
            }, function(res) {
                let rows = '';
                if (res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        const imageUrl = item.image ? `{{ asset('/') }}${item.image}` : 'https://placehold.co/50x50/EFEFEF/AAAAAA&text=No+Img';
                        const statusBadge = item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        
                        // Handle Null Relations
                        const featureName = item.feature ? item.feature.english_name : '<span class="text-muted">--</span>';
                        const parentName = item.parent ? item.parent.english_name : '<span class="text-muted">--</span>';
                        
                        // Color Box
                        const colorBox = item.color ? 
                            `<div class="d-flex align-items-center gap-2">
                                <div class="table-color-preview" style="background: ${item.color};"></div>
                                <small class="text-muted">${item.color}</small>
                             </div>` : '<span class="text-muted">--</span>';

                        rows += `<tr>
                            <td>${(res.current_page - 1) * res.per_page + i + 1}</td>
                            <td><img src="${imageUrl}" width="40" height="40" class="img-thumbnail"></td>
                            <td>${item.english_name}</td>
                            <td>${item.bangla_name}</td>
                            <td>${featureName}</td>
                            <td>${colorBox}</td>
                            <td>${parentName}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-info btn-edit text-white" data-id="${item.id}"><i class="fa fa-edit"></i></button>
                                <form action="${routes.delete(item.id)}" method="POST" class="d-inline delete-form">
                                    <input type="hidden" name="_token" value="${routes.csrf}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="button" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="9" class="text-center text-muted">No categories found</td></tr>';
                }
                $('#tableBody').html(rows);

                // Pagination
                let paginationHtml = '';
                if (res.last_page > 1) {
                    paginationHtml += `<li class="page-item ${res.current_page === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="1">First</a></li>`;
                    paginationHtml += `<li class="page-item ${res.current_page === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${res.current_page - 1}">Prev</a></li>`;
                    const startPage = Math.max(1, res.current_page - 2);
                    const endPage = Math.min(res.last_page, res.current_page + 2);
                    for (let i = startPage; i <= endPage; i++) {
                        paginationHtml += `<li class="page-item ${i === res.current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                    }
                    paginationHtml += `<li class="page-item ${res.current_page === res.last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${res.current_page + 1}">Next</a></li>`;
                    paginationHtml += `<li class="page-item ${res.current_page === res.last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${res.last_page}">Last</a></li>`;
                }
                $('#pagination').html(paginationHtml);
            });
        }

        // --- EVENT HANDLERS ---

        // Search
        $('#searchInput').on('keyup', function() {
            searchTerm = $(this).val();
            currentPage = 1;
            fetchData();
        });

        // Sort via Headers
        $(document).on('click', '.sortable', function() {
            let col = $(this).data('column');
            sortDirection = sortColumn === col ? (sortDirection === 'asc' ? 'desc' : 'asc') : 'asc';
            sortColumn = col;
            fetchData();
        });

        // Pagination Click
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            currentPage = $(this).data('page');
            fetchData();
        });

        // Edit Button Click
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');

            $.get(routes.show(id), function(item) {
                // Populate Fields
                $('#edit_english_name').val(item.english_name);
                $('#edit_bangla_name').val(item.bangla_name);
                
                // Select2 Set Value & Trigger Change
                $('#edit_feature_id').val(item.feature_id).trigger('change');
                $('#edit_parent_id').val(item.parent_id).trigger('change');

                // Color Set
                $('#edit_color').val(item.color);
                $('#edit_color_preview').css('background', item.color ? item.color : '#ddd');

                // Status
                $('#edit_status').val(item.status);

                // Image Preview
                if (item.image) {
                    $('#edit_image_preview').attr('src', `{{ asset('') }}${item.image}`).show();
                } else {
                    $('#edit_image_preview').hide();
                }

                // Update Form Action
                $('#editForm').attr('action', routes.update(id));

                // Open Modal
                editModal.show();
            });
        });

        // Delete Button (SweetAlert)
        $(document).on('click', '.btn-delete', function(e) {
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
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Reset Modals on Close
        $('#addModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $(this).find('.select2-modal').val('').trigger('change'); // Reset Select2
            $('#add_image_preview').hide().attr('src', '#');
            $('#add_color_preview').css('background', '#ddd');
        });

        // Initial Fetch
        fetchData();
    });
</script>
