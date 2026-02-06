@extends('admin.master.master')
@section('title', 'Book Details - ' . $book->title)

@section('css')
<style>
    .book-info-label { font-weight: 700; color: #444; width: 160px; display: inline-block; }
    .book-cover-detail { width: 100%; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border: 1px solid #ddd; }
    .badge-outline { border: 1px solid #ddd; color: #555; background: #f8f9fa; margin-right: 5px; }
    .pdf-frame { height: 600px; width: 100%; border: 1px solid #ddd; border-radius: 10px; }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <h4 class="mb-0">Book Details</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('book.edit', $book->id) }}" class="btn btn-info text-white">
                    <i class="fa fa-edit me-1"></i> Edit Book
                </a>
                <a href="{{ route('book.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>

        <div class="row">
            {{-- বাম কলাম: কভার ইমেজ এবং কুইক স্ট্যাটাস --}}
            <div class="col-md-3">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body text-center">
                        <img src="{{ asset('public/'.$book->image ?? 'public/assets/images/no-image.png') }}" class="book-cover-detail mb-3" alt="{{ $book->title }}">
                        <h5 class="fw-bold">{{ $book->title }}</h5>
                        <p class="text-muted small">ISBN: {{ $book->isbn_code ?? 'N/A' }}</p>
                        
                        <div class="d-grid gap-2">
                            @if($book->full_pdf)
                                <a href="{{ asset('public/'.$book->full_pdf) }}" download class="btn btn-success">
                                    <i class="fa fa-download me-2"></i> Download Full PDF
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light fw-bold">Price & Status</div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="book-info-label">Type:</span>
                            <span class="badge {{ $book->type == 'free' ? 'bg-success' : 'bg-primary' }}">
                                {{ ucfirst($book->type) }}
                            </span>
                        </div>
                        @if($book->type == 'paid')
                            <div class="mb-2">
                                <span class="book-info-label">Regular Price:</span>
                                <strong>{{ $book->price }} TK</strong>
                            </div>
                            <div class="mb-2">
                                <span class="book-info-label">Discount Price:</span>
                                <strong class="text-danger">{{ $book->discount_price }} TK</strong>
                            </div>
                        @endif
                        <div class="mb-0">
                            <span class="book-info-label">Total Downloads:</span>
                            <strong>{{ $book->total_download }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ডান কলাম: বিস্তারিত তথ্য এবং পিডিএফ প্রিভিউ --}}
            <div class="col-md-9">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="fa fa-info-circle text-primary me-2"></i> Information Details
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <p class="text-muted small mb-1">Book Category</p>
                                <h6 class="fw-bold">{{ $book->category->name_en ?? 'N/A' }}</h6>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="text-muted small mb-1">Subject</p>
                                <h6 class="fw-bold">{{ $book->subject->name_en ?? 'N/A' }}</h6>
                            </div>
                            <div class="col-md-12 mb-3">
                                <p class="text-muted small mb-1">Assigned Classes</p>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($book->schoolClasses as $cls)
                                        <span class="badge badge-outline p-2">{{ $cls->name_en }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <p class="text-muted small mb-1">Edition</p>
                                <h6 class="fw-bold">{{ $book->edition ?? 'N/A' }}</h6>
                            </div>
                            <div class="col-md-4 mb-3">
                                <p class="text-muted small mb-1">Language</p>
                                <h6 class="fw-bold">{{ $book->language }}</h6>
                            </div>
                            <div class="col-md-4 mb-3">
                                <p class="text-muted small mb-1">Publish Date</p>
                                <h6 class="fw-bold">{{ $book->publish_date ? date('M d, Y', strtotime($book->publish_date)) : 'N/A' }}</h6>
                            </div>
                            <div class="col-md-12">
                                <p class="text-muted small mb-1">Short Description</p>
                                <p>{{ $book->short_description ?? 'No description available.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PDF Preview --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-file-pdf text-danger me-2"></i> Preview (Short View)</span>
                        @if($book->preview_pdf)
                            <a href="{{ asset('public/'.$book->preview_pdf) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-external-link"></i> Open in New Tab
                            </a>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @if($book->preview_pdf)
                            <iframe src="{{ asset('public/'.$book->preview_pdf) }}#toolbar=0" class="pdf-frame"></iframe>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-file-pdf-o fa-3x text-light mb-2"></i>
                                <p class="text-muted">No preview PDF available.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection