@extends('admin.master.master')

@section('title') View MCQ | {{ $ins_name ?? 'App' }} @endsection

@section('css')
<style>
    /* --- Advanced Custom Styles --- */
    .text-primary-soft { color: #4e73df; }
    .bg-light-custom { background-color: #f8f9fc; }
    
    /* Option Cards */
    .option-card {
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        padding: 15px;
        transition: all 0.3s ease;
        background: #fff;
        height: 100%;
        position: relative;
    }
    .option-card:hover { border-color: #b7b9cc; }
    
    /* Correct Answer Style */
    .option-card.correct {
        border-color: #1cc88a;
        background-color: #f0fff9;
        box-shadow: 0 0 10px rgba(28, 200, 138, 0.1);
    }
    .option-icon {
        width: 30px; height: 30px;
        border-radius: 50%;
        background: #eaecf4;
        color: #5a5c69;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold;
        margin-right: 12px;
        flex-shrink: 0;
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
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* Meta Info List */
    .meta-list-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed #e3e6f0;
    }
    .meta-list-item:last-child { border-bottom: none; }
    .meta-icon {
        width: 35px; height: 35px;
        border-radius: 8px;
        background: rgba(78, 115, 223, 0.1);
        color: #4e73df;
        display: flex; align-items: center; justify-content: center;
        margin-right: 15px;
    }
    .meta-label { font-size: 0.8rem; color: #858796; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
    .meta-value { font-weight: 600; color: #5a5c69; font-size: 0.95rem; }

    /* Tags */
    .tag-badge {
        background: #eaecf4;
        color: #5a5c69;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        margin-right: 5px;
        margin-bottom: 5px;
        display: inline-block;
    }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 text-gray-800">MCQ Details</h2>
                <p class="text-muted small mb-0">Question ID: #{{ $mcq->id }} | Created: {{ $mcq->created_at->format('d M, Y') }}</p>
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
            {{-- Left Column: Question, Options, Description --}}
            <div class="col-lg-8">
                
                {{-- Question Card --}}
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-white py-3 d-flex align-items-center border-bottom-0">
                        <div class="meta-icon bg-primary-soft text-primary">
                            <i data-feather="help-circle"></i>
                        </div>
                        <h5 class="m-0 font-weight-bold text-primary">Question Statement</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="p-3 bg-light-custom rounded border fs-5 text-dark">
                            {!! $mcq->question !!}
                        </div>
                    </div>
                </div>

                {{-- Options Section --}}
                <div class="mb-4">
                    <h5 class="mb-3 ps-1 text-gray-800"><i data-feather="list" class="me-2" style="width:18px;"></i>Options</h5>
                    <div class="row g-3">
                        @for($i=1; $i<=4; $i++)
                        @php 
                            $optKey = 'option_'.$i; 
                            $isCorrect = ($mcq->answer == $i);
                        @endphp
                        <div class="col-md-6">
                            <div class="option-card {{ $isCorrect ? 'correct' : '' }} d-flex align-items-center">
                                <div class="option-icon">{{ $i }}</div>
                                <div class="flex-grow-1">
                                    {{ $mcq->$optKey }}
                                </div>
                                @if($isCorrect)
                                    <div class="check-mark"><i class="fa fa-check"></i></div>
                                @endif
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>

                {{-- Explanation --}}
                @if($mcq->short_description)
                <div class="card shadow-sm border-0 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="me-3 text-info"><i data-feather="info" style="width: 30px; height: 30px;"></i></div>
                            <div>
                                <h6 class="fw-bold text-info mb-1">Explanation / Note</h6>
                                <p class="mb-0 text-muted">{{ $mcq->short_description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Column: Meta Info --}}
            <div class="col-lg-4">
                
                {{-- Hierarchy Info Card --}}
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 font-weight-bold text-dark">Academic Hierarchy</h6>
                    </div>
                    <div class="card-body pt-2 pb-2">
                        {{-- Class --}}
                        <div class="meta-list-item">
                            <div class="meta-icon"><i data-feather="layers"></i></div>
                            <div>
                                <div class="meta-label">Class</div>
                                <div class="meta-value">{{ $mcq->class->name_en ?? '--' }}</div>
                            </div>
                        </div>
                        {{-- Subject --}}
                        <div class="meta-list-item">
                            <div class="meta-icon bg-success-soft text-success"><i data-feather="book"></i></div>
                            <div>
                                <div class="meta-label">Subject</div>
                                <div class="meta-value">{{ $mcq->subject->name_en ?? '--' }}</div>
                            </div>
                        </div>
                        {{-- Department --}}
                        <div class="meta-list-item">
                            <div class="meta-icon bg-warning-soft text-warning"><i data-feather="grid"></i></div>
                            <div>
                                <div class="meta-label">Department</div>
                                <div class="meta-value">{{ $mcq->department->name_en ?? 'N/A' }}</div>
                            </div>
                        </div>
                        {{-- Chapter --}}
                        <div class="meta-list-item">
                            <div class="meta-icon bg-info-soft text-info"><i data-feather="bookmark"></i></div>
                            <div>
                                <div class="meta-label">Chapter</div>
                                <div class="meta-value">{{ $mcq->chapter->name_en ?? '--' }}</div>
                            </div>
                        </div>
                        {{-- Topic --}}
                        <div class="meta-list-item">
                            <div class="meta-icon bg-danger-soft text-danger"><i data-feather="tag"></i></div>
                            <div>
                                <div class="meta-label">Topic</div>
                                <div class="meta-value">{{ $mcq->topic->name_en ?? '--' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Administrative Info Card --}}
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 font-weight-bold text-dark">Administrative Info</h6>
                    </div>
                    <div class="card-body pt-2 pb-2">
                        <div class="meta-list-item">
                            <div class="meta-label w-50">Institute</div>
                            <div class="meta-value text-end w-50">{{ $mcq->institute->name_en ?? '--' }}</div>
                        </div>
                        <div class="meta-list-item">
                            <div class="meta-label w-50">Board</div>
                            <div class="meta-value text-end w-50">{{ $mcq->board->name_en ?? '--' }}</div>
                        </div>
                        <div class="meta-list-item">
                            <div class="meta-label w-50">Academic Year</div>
                            <div class="meta-value text-end w-50">{{ $mcq->academicYear->name_en ?? '--' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Status & Tags --}}
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                            <div>
                                <div class="meta-label">Upload Type</div>
                                <span class="badge bg-secondary mt-1">{{ ucfirst($mcq->upload_type) }}</span>
                            </div>
                            <div class="text-end">
                                <div class="meta-label">Status</div>
                                @if($mcq->status == 1)
                                    <span class="badge bg-success mt-1"><i class="fa fa-check-circle me-1"></i> Active</span>
                                @else
                                    <span class="badge bg-danger mt-1"><i class="fa fa-times-circle me-1"></i> Inactive</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="meta-label mb-2">Tags</div>
                            @if($mcq->tags && count($mcq->tags) > 0)
                                <div>
                                    @foreach($mcq->tags as $tag)
                                        <span class="tag-badge">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted small fst-italic">No tags added.</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>
@endsection

@section('script')
<script>
    // Re-initialize feather icons if loaded dynamically (just in case)
    feather.replace();
</script>
@endsection