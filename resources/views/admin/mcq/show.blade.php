@extends('admin.master.master')

@section('title') View MCQ | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<style>
    /* পূর্বের ডিজাইন বজায় রাখা হয়েছে */
    .text-primary-soft { color: #4e73df; }
    .bg-light-custom { background-color: #f8f9fc; }
    
    .option-card {
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        padding: 15px;
        transition: all 0.3s ease;
        background: #fff;
        height: 100%;
        position: relative;
    }
    
    .option-card.correct {
        border-color: #1cc88a;
        background-color: #f0fff9;
        box-shadow: 0 0 10px rgba(28, 200, 138, 0.1);
    }

    .correct .option-icon {
        background: #1cc88a;
        color: white;
    }

    .check-mark {
        position: absolute;
        top: -10px; right: -10px;
        background: #1cc88a; color: white;
        width: 25px; height: 25px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px;
    }

    /* নতুন ইমেজ ডিসপ্লে স্টাইল */
    .mcq-img-container {
        text-align: center;
        padding: 10px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
    }
    .mcq-img-container img {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
    }

    .meta-list-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed #e3e6f0;
    }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 text-gray-800">MCQ Details</h2>
                <p class="text-muted small mb-0">
                    Type: <span class="badge {{ $mcq->mcq_type == 'image' ? 'bg-danger' : 'bg-primary' }} text-white">{{ ucfirst($mcq->mcq_type) }}</span> 
                    | ID: #{{ $mcq->id }} | Created: {{ $mcq->created_at->format('d M, Y') }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('mcq.index') }}" class="btn btn-secondary shadow-sm">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
                <a href="{{ route('mcq.edit', $mcq->id) }}" class="btn btn-info text-white shadow-sm">
                    <i class="fa fa-edit me-1"></i> Edit MCQ
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Question Card --}}
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="m-0 font-weight-bold text-primary">Question Statement</h5>
                    </div>
                    <div class="card-body pt-0">
                        @if($mcq->mcq_type == 'image' && $mcq->question_img)
                            <div class="mcq-img-container">
                                <img src="{{ asset($mcq->question_img) }}" alt="Question Image">
                            </div>
                        @else
                            <div class="p-3 bg-light-custom rounded border fs-5 text-dark">
                                {!! $mcq->question !!}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Options Section --}}
                <div class="mb-4">
                    <h5 class="mb-3 ps-1 text-gray-800"><i class="fa fa-list me-2"></i>Options</h5>
                    <div class="row g-3">
                        @for($i=1; $i<=4; $i++)
                        @php 
                            $optKey = 'option_'.$i; 
                            $optImgKey = 'option_'.$i.'_img';
                            $isCorrect = ($mcq->answer == $i);
                        @endphp
                        <div class="col-md-6">
                            <div class="option-card {{ $isCorrect ? 'correct' : '' }}">
                                <div class="d-flex align-items-center">
                                    <div class="option-icon bg-light p-2 rounded-circle me-3 fw-bold">{{ $i }}</div>
                                    <div class="flex-grow-1">
                                        @if($mcq->mcq_type == 'image' && $mcq->$optImgKey)
                                            <div class="text-center">
                                                <img src="{{ asset($mcq->$optImgKey) }}" class="img-fluid rounded" style="max-height: 100px;">
                                            </div>
                                        @else
                                            {{ $mcq->$optKey }}
                                        @endif
                                    </div>
                                    @if($isCorrect)
                                        <div class="check-mark"><i class="fa fa-check"></i></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- Explanation --}}
                @if($mcq->short_description)
                <div class="card shadow-sm border-0 border-start border-4 border-info">
                    <div class="card-body">
                        <h6 class="fw-bold text-info"><i class="fa fa-info-circle me-1"></i> Explanation</h6>
                        <p class="mb-0 text-muted">{{ $mcq->short_description }}</p>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                {{-- Academic Hierarchy --}}
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 font-weight-bold text-dark">Academic Hierarchy</h6>
                    </div>
                    <div class="card-body pt-2 pb-2">
                        <div class="meta-list-item">
                            <div><div class="meta-label">Class</div><div class="meta-value">{{ $mcq->class->name_en ?? '--' }}</div></div>
                        </div>
                        <div class="meta-list-item">
                            <div><div class="meta-label">Section (New)</div><div class="meta-value">{{ $mcq->section->name_en ?? 'N/A' }}</div></div>
                        </div>
                        <div class="meta-list-item">
                            <div><div class="meta-label">Subject</div><div class="meta-value">{{ $mcq->subject->name_en ?? '--' }}</div></div>
                        </div>
                        <div class="meta-list-item">
                            <div><div class="meta-label">Chapter</div><div class="meta-value">{{ $mcq->chapter->name_en ?? '--' }}</div></div>
                        </div>
                    </div>
                </div>

                {{-- Multiple Data: Institutes & Boards --}}
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 font-weight-bold text-dark">Administrative Info (Multiple)</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="meta-label mb-2">Institutes</div>
                            @if(!empty($mcq->institute_ids))
                                @php $institutes = \App\Models\Institute::whereIn('id', $mcq->institute_ids)->pluck('name_en'); @endphp
                                @foreach($institutes as $name)
                                    <span class="badge bg-light text-dark border mb-1">{{ $name }}</span>
                                @endforeach
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </div>
                        <hr>
                        <div>
                            <div class="meta-label mb-2">Boards</div>
                            @if(!empty($mcq->board_ids))
                                @php $boards = \App\Models\Board::whereIn('id', $mcq->board_ids)->pluck('name_en'); @endphp
                                @foreach($boards as $name)
                                    <span class="badge bg-light text-primary border mb-1">{{ $name }}</span>
                                @endforeach
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Status & Tags --}}
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="meta-label">Status</span>
                            <span class="badge {{ $mcq->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                {{ $mcq->status == 1 ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="meta-label mb-2">Tags</div>
                        @if($mcq->tags)
                            @foreach($mcq->tags as $tag)
                                <span class="badge bg-secondary mb-1">{{ $tag }}</span>
                            @endforeach
                        @else
                            <span class="text-muted small">No tags.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection