@extends('admin.master.master')
@section('title', 'Edit Book - ' . $book->title)

@section('css')
{{-- Select2 & Flatpickr CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    .card-header { font-size: 1.1rem; font-weight: 600; }
    .form-label { font-weight: 600; color: #444; }
    #preview_img { 
        max-height: 200px; 
        border: 2px dashed #ddd; 
        padding: 5px; 
        border-radius: 8px; 
    }
    .current-file-info { font-size: 0.85rem; color: #666; margin-top: 5px; display: block; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-11 mx-auto mt-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white"><i class="fa fa-edit me-2"></i>Edit Book: {{ $book->title }}</h5>
                        <a href="{{ route('book.index') }}" class="btn btn-sm btn-light">
                            <i class="fa fa-list me-1"></i> Back to List
                        </a>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('book.update', $book->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf 
                            @method('PUT')
                            
                            <div class="row">
                                {{-- ১. টাইটেল ও পাবলিকেশন কোড --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Book Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title', $book->title) }}" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">ISBN / Book Code</label>
                                    <input type="text" name="isbn_code" class="form-control" value="{{ old('isbn_code', $book->isbn_code) }}">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Edition</label>
                                    <input type="text" name="edition" class="form-control" value="{{ old('edition', $book->edition) }}">
                                </div>

                                {{-- ২. ক্যাটাগরি, ক্লাস ও সাবজেক্ট --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Book Category <span class="text-danger">*</span></label>
                                    <select name="book_category_id" class="form-control select2" required>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ $book->book_category_id == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Assign to Classes (Multiple) <span class="text-danger">*</span></label>
                                    <select name="school_class_ids[]" id="class_select" class="form-control select2" multiple="multiple" required>
                                        @foreach($classes as $cls)
                                            <option value="{{ $cls->id }}" {{ in_array($cls->id, $selectedClasses) ? 'selected' : '' }}>
                                                {{ $cls->name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Subject</label>
                                    <select name="subject_id" id="subject_select" class="form-control select2">
                                        <option value="">-- Select Class First --</option>
                                        @if($book->subject_id)
                                            <option value="{{ $book->subject_id }}" selected>{{ $book->subject->name_en ?? '' }}</option>
                                        @endif
                                    </select>
                                </div>

                                {{-- ৩. পাবলিকেশন তথ্য ও ডেইট পিকার --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Language</label>
                                    <input type="text" name="language" class="form-control" value="{{ old('language', $book->language) }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Authority / Author</label>
                                    <input type="text" name="authority" class="form-control" value="{{ old('authority', $book->authority) }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Publish Date</label>
                                    <div class="input-group">
                                        <input type="text" name="publish_date" id="publish_date" class="form-control datepicker" value="{{ old('publish_date', $book->publish_date) }}">
                                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>

                                {{-- ৪. ফাইল প্রিভিউ ও আপলোড --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cover Image</label>
                                    <input type="file" name="image" id="image_input" class="form-control" accept="image/*">
                                    <div class="mt-2 text-center">
                                        <img id="preview_img" src="{{ asset('public/'.$book->image ?? 'public/assets/images/no-image.png') }}" alt="Preview">
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-info">Preview PDF (Short)</label>
                                    <input type="file" name="preview_pdf" class="form-control" accept=".pdf">
                                    @if($book->preview_pdf)
                                        <a href="{{ asset('public/'.$book->preview_pdf) }}" target="_blank" class="current-file-info">
                                            <i class="fa fa-file-pdf text-danger"></i> View Current Preview PDF
                                        </a>
                                    @endif
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-success">Full PDF (Main File)</label>
                                    <input type="file" name="full_pdf" class="form-control" accept=".pdf">
                                    @if($book->full_pdf)
                                        <a href="{{ asset('public/'.$book->full_pdf) }}" target="_blank" class="current-file-info">
                                            <i class="fa fa-file-pdf text-danger"></i> View Current Full PDF
                                        </a>
                                    @endif
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Short Description</label>
                                    <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $book->short_description) }}</textarea>
                                </div>

                                {{-- ৫. টাইপ ও প্রাইসিং --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Type</label>
                                    <select name="type" id="type_select" class="form-control">
                                        <option value="free" {{ $book->type == 'free' ? 'selected' : '' }}>Free</option>
                                        <option value="paid" {{ $book->type == 'paid' ? 'selected' : '' }}>Paid</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3 price-fields" style="{{ $book->type == 'paid' ? 'display:block' : 'display:none' }}">
                                    <label class="form-label">Regular Price</label>
                                    <input type="number" name="price" class="form-control" value="{{ old('price', $book->price) }}">
                                </div>

                                <div class="col-md-4 mb-3 price-fields" style="{{ $book->type == 'paid' ? 'display:block' : 'display:none' }}">
                                    <label class="form-label">Discount Price</label>
                                    <input type="number" name="discount_price" class="form-control" value="{{ old('discount_price', $book->discount_price) }}">
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-info text-white px-5 py-2">
                                    <i class="fa fa-save me-1"></i> Update Book
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    $(document).ready(function() {
        // ১. Select2
        $('.select2').select2({ width: '100%' });

        // ২. Flatpickr (Date Picker)
        flatpickr("#publish_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            allowInput: true
        });

        // ৩. ইমেজ প্রিভিউ
        $('#image_input').change(function() {
            let reader = new FileReader();
            reader.onload = (e) => { 
                $('#preview_img').attr('src', e.target.result); 
            }
            if(this.files[0]) reader.readAsDataURL(this.files[0]);
        });

        // ৪. টাইপ অনুযায়ী প্রাইস শো/হাইড
        $('#type_select').change(function() {
            if($(this).val() === 'paid') $('.price-fields').fadeIn();
            else $('.price-fields').fadeOut();
        });

        // ৫. ডিপেন্ডেন্ট সাবজেক্ট লোডিং
        function loadSubjects(classIds) {
            let subjectDropdown = $('#subject_select');
            let currentSubjectId = "{{ $book->subject_id }}";

            if (classIds && classIds.length > 0) {
                $.ajax({
                    url: "{{ route('book.getSubjects') }}",
                    type: "GET",
                    data: { class_ids: classIds },
                    success: function(res) {
                        let options = '<option value="">-- Select Subject --</option>';
                        res.forEach(function(subject) {
                            let selected = (subject.id == currentSubjectId) ? 'selected' : '';
                            options += `<option value="${subject.id}" ${selected}>${subject.name_en}</option>`;
                        });
                        subjectDropdown.html(options);
                    }
                });
            } else {
                subjectDropdown.html('<option value="">-- Select Class First --</option>');
            }
        }

        // পেজ লোড হওয়ার সময় সাবজেক্ট লোড করা
        loadSubjects($('#class_select').val());

        // ক্লাস পরিবর্তন হলে সাবজেক্ট আপডেট
        $('#class_select').on('change', function() {
            loadSubjects($(this).val());
        });
    });
</script>
@endsection