@extends('admin.master.master')

@section('title') Edit Feature | {{ $ins_name ?? 'App' }} @endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Edit Feature</h2>
            <a href="{{ route('feature-list.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <form action="{{ route('feature-list.update', $feature->id) }}" method="POST" class="card shadow-sm">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @include('flash_message')

                        <div class="mb-3">
                            <label class="form-label">Feature Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $feature->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">System Code (Unique)</label>
                            <input type="text" class="form-control bg-light" value="{{ $feature->code }}" readonly>
                            <small class="text-info">System code is auto-updated if name changes significantly.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="1" {{ $feature->status == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $feature->status == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-sync me-1"></i> Update Feature</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection