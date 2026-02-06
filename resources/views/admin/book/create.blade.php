@extends('admin.master.master')
@section('title', 'Add New Book')

@section('css')
{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
{{-- Flatpickr CSS (Date Picker) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    .card-header { font-size: 1.1rem; font-weight: 600; }
    .form-label { font-weight: 600; color: #444; }
    .price-fields { display: none; }
    #preview_img { 
        max-height: 200px; 
        border: 2px dashed #ddd; 
        padding: 5px; 
        border-radius: 8px; 
        display: none;
    }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-11 mx-auto mt-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-book-medical me-2"></i>Add New Book / PDF</span>
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

                        <form action="{{ route('book.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                {{-- সাধারণ তথ্য --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Book Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">ISBN / Book Code</label>
                                    <input type="text" name="isbn_code" class="form-control" value="{{ old('isbn_code') }}">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Edition</label>
                                    <input type="text" name="edition" class="form-control" value="{{ old('edition') }}" placeholder="e.g. 2026">
                                </div>

                                {{-- ক্যাটাগরি, ক্লাস ও সাবজেক্ট --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Book Category <span class="text-danger">*</span></label>
                                    <select name="book_category_id" class="form-control select2" required>
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Assign to Classes (Multiple) <span class="text-danger">*</span></label>
                                    <select name="school_class_ids[]" id="class_select" class="form-control select2" multiple="multiple" required>
                                        @foreach($classes as $cls)
                                            <option value="{{ $cls->id }}">{{ $cls->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Subject</label>
                                    <select name="subject_id" id="subject_select" class="form-control select2">
                                        <option value="">-- Select Class First --</option>
                                    </select>
                                </div>

                                {{-- পাবলিকেশন ও ডেট পিকার --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Language</label>
                                    <input type="text" name="language" class="form-control" value="{{ old('language', 'Bangla') }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Authority / Author</label>
                                    <input type="text" name="authority" class="form-control" value="{{ old('authority') }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Publish Date</label>
                                    {{-- ডেট পিকারের জন্য এখানে 'datepicker' ক্লাস যোগ করা হয়েছে --}}
                                    <div class="input-group">
                                        <input type="text" name="publish_date" id="publish_date" class="form-control datepicker" placeholder="Select Date" value="{{ old('publish_date') }}">
                                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>

                                {{-- ফাইল ও ইমেজ প্রিভিউ --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cover Image</label>
                                    <input type="file" name="image" id="image_input" class="form-control" accept="image/*">
                                    <div class="mt-2">
                                        <img id="preview_img" src="#" alt="Preview">
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3 text-info">
                                    <label class="form-label">Preview PDF (Short)</label>
                                    <input type="file" name="preview_pdf" class="form-control" accept=".pdf">
                                </div>

                                <div class="col-md-4 mb-3 text-success">
                                    <label class="form-label">Full PDF (Main File)</label>
                                    <input type="file" name="full_pdf" class="form-control" accept=".pdf">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Short Description</label>
                                    <textarea name="short_description" class="form-control" rows="2">{{ old('short_description') }}</textarea>
                                </div>

                                {{-- টাইপ ও প্রাইসিং --}}
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Type</label>
                                    <select name="type" id="type_select" class="form-control">
                                        <option value="free">Free</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3 price-fields">
                                    <label class="form-label">Regular Price</label>
                                    <input type="number" name="price" class="form-control" value="0">
                                </div>

                                <div class="col-md-4 mb-3 price-fields">
                                    <label class="form-label">Discount Price</label>
                                    <input type="number" name="discount_price" class="form-control" value="0">
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary px-5 py-2">
                                    <i class="fa fa-save me-1"></i> Save Book
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
{{-- Flatpickr JS --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    $(document).ready(function() {
        // ১. Select2
        $('.select2').select2({ width: '100%' });

        // ২. Flatpickr Date Picker ইনিশিয়ালাইজেশন
        flatpickr("#publish_date", {
            dateFormat: "Y-m-d", // ডাটাবেসে সেভ করার ফরম্যাট
            altInput: true,      // ইউজারকে সুন্দর ফরম্যাট দেখানোর জন্য
            altFormat: "F j, Y", // ইউজারের দেখার জন্য ফরম্যাট (যেমন: February 6, 2026)
            allowInput: true
        });

        // ৩. ইমেজ প্রিভিউ
        $('#image_input').change(function() {
            let reader = new FileReader();
            reader.onload = (e) => { 
                $('#preview_img').attr('src', e.target.result).fadeIn(); 
            }
            if(this.files[0]) reader.readAsDataURL(this.files[0]);
        });

        // ৪. টাইপ অনুযায়ী প্রাইস শো/হাইড
        $('#type_select').change(function() {
            if($(this).val() === 'paid') $('.price-fields').fadeIn();
            else $('.price-fields').fadeOut();
        });

        // ৫. ডাইনামিক সাবজেক্ট লোডিং
        $('#class_select').on('change', function() {
            let classIds = $(this).val(); 
            let subjectDropdown = $('#subject_select');
            if (classIds && classIds.length > 0) {
                $.ajax({
                    url: "{{ route('book.getSubjects') }}",
                    type: "GET",
                    data: { class_ids: classIds },
                    success: function(res) {
                        let options = '<option value="">-- Select Subject --</option>';
                        res.forEach(function(subject) {
                            options += `<option value="${subject.id}">${subject.name_en}</option>`;
                        });
                        subjectDropdown.html(options);
                    }
                });
            } else {
                subjectDropdown.html('<option value="">-- Select Class First --</option>');
            }
        });
    });
</script>
@endsection