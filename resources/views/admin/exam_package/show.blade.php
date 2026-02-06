@extends('admin.master.master')
@section('title', 'Exam Package Details')

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Exam Package Details</h5>
                        <div>
                            <a href="{{ route('exam-package.edit', $package->id) }}" class="btn btn-sm btn-info text-white me-1">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('exam-package.index') }}" class="btn btn-sm btn-light">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-striped table-bordered">
                                    <tr>
                                        <th width="40%">Exam Name</th>
                                        <td><strong>{{ $package->exam_name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Class</th>
                                        <td>{{ $package->schoolClass->name_en ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Department</th>
                                        <td>{{ $package->department->name_en ?? 'No Department' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Exam Type</th>
                                        <td>
                                            <span class="badge {{ $package->exam_type == 'free' ? 'bg-success' : 'bg-primary' }}">
                                                {{ ucfirst($package->exam_type) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Price</th>
                                        <td>{{ $package->price > 0 ? $package->price . ' TK' : 'Free' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Validity</th>
                                        <td>{{ $package->validity_days }} Days</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6 text-center d-flex align-items-center justify-content-center">
                                <div class="p-4 border rounded bg-light">
                                    <h1 class="display-4 text-primary">{{ count($package->subject_ids ?? []) }}</h1>
                                    <p class="text-muted">Total Subjects Included</p>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row mt-4">
                            {{-- Subjects Column --}}
                            <div class="col-md-4">
                                <h6 class="fw-bold text-primary border-bottom pb-2">Included Subjects</h6>
                                <ul class="list-group list-group-flush">
                                    @forelse($package->subjects as $sub)
                                        <li class="list-group-item"><i class="fa fa-check-circle text-success me-2"></i> {{ $sub->name_en }}</li>
                                    @empty
                                        <li class="list-group-item text-muted small">No subjects selected</li>
                                    @endforelse
                                </ul>
                            </div>

                            {{-- Chapters Column --}}
                            <div class="col-md-4">
                                <h6 class="fw-bold text-success border-bottom pb-2">Included Chapters</h6>
                                <ul class="list-group list-group-flush">
                                    @forelse($package->chapters as $chap)
                                        <li class="list-group-item small"><i class="fa fa-book me-2"></i> {{ $chap->name_en }}</li>
                                    @empty
                                        <li class="list-group-item text-muted small">No chapters selected</li>
                                    @endforelse
                                </ul>
                            </div>

                            {{-- Topics Column --}}
                            <div class="col-md-4">
                                <h6 class="fw-bold text-info border-bottom pb-2">Included Topics</h6>
                                <ul class="list-group list-group-flush">
                                    @forelse($package->topics as $top)
                                        <li class="list-group-item small"><i class="fa fa-tag me-2"></i> {{ $top->name_en }}</li>
                                    @empty
                                        <li class="list-group-item text-muted small">No topics selected</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection