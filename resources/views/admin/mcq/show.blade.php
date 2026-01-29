@extends('admin.master.master')

@section('title') View MCQ | {{ $ins_name ?? 'App' }} @endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">MCQ Details</h2>
            <div>
                <a href="{{ route('mcq.edit', $mcq->id) }}" class="btn btn-info text-white"><i class="fa fa-edit me-1"></i> Edit</a>
                <a href="{{ route('mcq.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left me-1"></i> Back</a>
            </div>
        </div>

        <div class="row">
            {{-- Question Card --}}
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Question</h5>
                    </div>
                    <div class="card-body">
                        <div class="fs-5 mb-3">{!! $mcq->question !!}</div>
                        
                        <div class="list-group">
                            @for($i=1; $i<=4; $i++)
                            @php $opt = 'option_'.$i; @endphp
                            <div class="list-group-item {{ $mcq->answer == $i ? 'list-group-item-success' : '' }}">
                                <span class="fw-bold me-2">{{ $i }}.</span> {{ $mcq->$opt }}
                                @if($mcq->answer == $i) <i class="fa fa-check-circle float-end"></i> @endif
                            </div>
                            @endfor
                        </div>

                        @if($mcq->short_description)
                        <div class="alert alert-info mt-3">
                            <strong><i class="fa fa-info-circle"></i> Explanation:</strong>
                            <p class="mb-0">{{ $mcq->short_description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Metadata Card --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light"><h5 class="mb-0">Info</h5></div>
                    <table class="table table-bordered mb-0">
                        <tr><th>Class</th><td>{{ $mcq->class->name_en ?? '--' }}</td></tr>
                        <tr><th>Subject</th><td>{{ $mcq->subject->name_en ?? '--' }}</td></tr>
                        <tr><th>Chapter</th><td>{{ $mcq->chapter->name_en ?? '--' }}</td></tr>
                        <tr><th>Topic</th><td>{{ $mcq->topic->name_en ?? '--' }}</td></tr>
                        <tr><th>Category</th><td>{{ $mcq->category->name_en ?? '--' }}</td></tr>
                        <tr><th>Institute</th><td>{{ $mcq->institute->name_en ?? '--' }}</td></tr>
                        <tr><th>Board</th><td>{{ $mcq->board->name_en ?? '--' }}</td></tr>
                        <tr><th>Year</th><td>{{ $mcq->academicYear->name_en ?? '--' }}</td></tr>
                        <tr><th>Upload Type</th><td><span class="badge bg-secondary">{{ ucfirst($mcq->upload_type) }}</span></td></tr>
                        <tr><th>Status</th><td>{!! $mcq->status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}</td></tr>
                    </table>
                    <div class="card-body">
                        <strong>Tags:</strong>
                        @if($mcq->tags)
                            @foreach($mcq->tags as $tag) <span class="badge bg-light text-dark border">{{ $tag }}</span> @endforeach
                        @else -- @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection