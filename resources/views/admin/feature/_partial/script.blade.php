<script>

    // --- IMAGE PREVIEW FUNCTION ---
    function previewImage(input, previewId) {
        var preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block'; // ছবি সিলেক্ট করলে শো করবে
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            // যদি এডিট মোডাল না হয়, তাহলে ছবি হাইড করে দিবে
            if(previewId === 'add_image_preview') {
                preview.style.display = 'none';
            }
        }
    }
    // --- COLOR PICKER HELPER FUNCTION ---
    // যখন কালার পিকার চেঞ্জ হবে, তখন টেক্সট বক্সে ভ্যালু বসবে এবং প্রিভিউ আপডেট হবে
    function updateColorInput(picker, inputId, previewId) {
        document.getElementById(inputId).value = picker.value;
        document.getElementById(previewId).style.background = picker.value;
    }

    $(document).ready(function() {


        // --- DRAG AND DROP LOGIC ---
        $("#sortable-list").sortable({
            placeholder: "ui-state-highlight",
            update: function(event, ui) {
                var order = [];
                // লুপ চালিয়ে নতুন সিরিয়াল নেওয়া হচ্ছে
                $('#sortable-list li').each(function(index) {
                    order.push({
                        id: $(this).data('id'),
                        position: index + 1
                    });
                });

                // AJAX দিয়ে ডাটাবেসে পাঠানো
                $.ajax({
                    url: "{{ route('feature.reorder') }}",
                    type: 'POST',
                    data: {
                        order: order,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        const Toast = Swal.mixin({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true
                        });
                        Toast.fire({ icon: 'success', title: 'Sort order updated successfully' });
                        
                        // টেবিল ডাটা রিফ্রেশ করা (যাতে সিরিয়াল ঠিক থাকে)
                        fetchData(); 
                    },
                    error: function() {
                        Swal.fire('Error', 'Something went wrong!', 'error');
                    }
                });
            }
        });
        $("#sortable-list").disableSelection();
        // --- LIVE PREVIEW ON TEXT INPUT ---
        // ইউজার যখন টেক্সট বক্সে Gradient বা RGB কোড পেস্ট করবে, সাথে সাথে প্রিভিউ দেখাবে
        $('#add_color_input').on('input', function() {
            $('#add_color_preview').css('background', $(this).val());
        });
        $('#edit_color').on('input', function() {
            $('#edit_color_preview').css('background', $(this).val());
        });

        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
        var currentPage = 1, searchTerm = '', sortColumn = 'id', sortDirection = 'desc';

        var routes = {
            fetch: "{{ route('ajax.feature.data') }}",
            show: id => `{{ route('feature.show', ':id') }}`.replace(':id', id),
            update: id => `{{ route('feature.update', ':id') }}`.replace(':id', id),
            delete: id => `{{ route('feature.destroy', ':id') }}`.replace(':id', id),
            csrf: "{{ csrf_token() }}"
        };

        function fetchData() {
            $.get(routes.fetch, {
                page: currentPage, search: searchTerm, sort: sortColumn, direction: sortDirection
            }, function (res) {
                let rows = '';
                if(res.data.length > 0) {
                    res.data.forEach((item, i) => {
                        const imageUrl = item.image ? `{{ asset('/') }}public/${item.image}` : 'https://placehold.co/80x80/EFEFEF/AAAAAA&text=No+Image';
                        const statusBadge = item.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        const parentName = item.parent ? item.parent.english_name : '<span class="text-muted">--</span>';
                        
                        // Color Preview in Table
                        const colorBox = `<div class="d-flex align-items-center gap-2">
                                            <div class="table-color-preview" style="background: ${item.color};"></div> 
                                            <small class="text-muted" style="max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${item.color}</small>
                                          </div>`;

                        rows += `<tr>
                            <td>${(res.current_page - 1) * res.per_page + i + 1}</td>
                            <td><img src="${imageUrl}" alt="Icon" width="50" height="50" class="img-thumbnail"></td>
                            <td>${item.english_name}</td>
                            <td>${item.bangla_name}</td>
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
                    rows = '<tr><td colspan="8" class="text-center text-muted">No features found</td></tr>';
                }
                $('#tableBody').html(rows);

                // Pagination logic remains same...
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

        // Search, Sort, Pagination Bindings (Same as before)
        $('#searchInput').on('keyup', function () { searchTerm = $(this).val(); currentPage = 1; fetchData(); });
        $(document).on('click', '.sortable', function () {
            let col = $(this).data('column');
            sortDirection = sortColumn === col ? (sortDirection === 'asc' ? 'desc' : 'asc') : 'asc';
            sortColumn = col;
            fetchData();
        });
        $(document).on('click', '.page-link', function (e) { e.preventDefault(); currentPage = $(this).data('page'); fetchData(); });

        // EDIT BUTTON CLICK
        $(document).on('click', '.btn-edit', function () {
            const id = $(this).data('id');
            $.get(routes.show(id), function (item) {
                $('#edit_english_name').val(item.english_name);
                $('#edit_bangla_name').val(item.bangla_name);
                $('#edit_parent_id').val(item.parent_id);
                
                // Set Color Value and Preview
                $('#edit_color').val(item.color);
                $('#edit_color_preview').css('background', item.color);

                $('#edit_short_description').val(item.short_description);
                $('#edit_status').val(item.status);

                if (item.image) {
                    $('#edit_image_preview').attr('src', `{{ asset('') }}${item.image}`).show();
                } else {
                    $('#edit_image_preview').hide();
                }

                $('#editForm').attr('action', routes.update(id));
                editModal.show();
            });
        });

        // Delete (SweetAlert)
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
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        fetchData();
    });
</script>