@extends('admin.master.master')

@section('title') Add New Feature | {{ $ins_name ?? 'App' }} @endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Add New Feature</h2>
            <a href="{{ route('feature-list.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <form action="{{ route('feature-list.store') }}" method="POST" class="card shadow-sm">
                    @csrf
                    <div class="card-body">
                        @include('flash_message')

                        <div class="mb-3">
                            <label class="form-label">Feature Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., Free Model Test">
                            <small class="text-muted">A unique system code will be generated based on this name.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Save Feature</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection