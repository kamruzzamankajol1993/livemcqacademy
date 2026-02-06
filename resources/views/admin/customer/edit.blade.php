@extends('admin.master.master')
@section('title', 'Edit Student Profile')

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Edit Student Login Access</h2>
            <a href="{{ route('student.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">Update Information for {{ $customer->name }}</h5>
                    </div>
                    <div class="card-body">
                        @include('flash_message')

                        <form action="{{ route('student.update', $customer->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}" required>
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label class="form-label fw-bold">Email Address (Login Username) <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}" required>
                                </div>

                                <hr>
                                <div class="col-md-12">
                                    <p class="text-warning small mb-3"><i class="fa fa-exclamation-triangle"></i> Leave password fields empty if you don't want to change it.</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">New Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="Enter new password">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password">
                                </div>
                            </div>

                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-success px-4 shadow-sm">
                                    <i class="fa fa-sync me-1"></i> Update Student & User Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            {{-- বর্তমান স্ট্যাটাস কার্ড --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body text-center py-4">
                        <i class="fa fa-user-circle fa-4x text-muted mb-3"></i>
                        <h5>Current Account Info</h5>
                        <p class="mb-0"><strong>Joined:</strong> {{ $customer->created_at->format('d M, Y') }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge {{ $customer->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                {{ $customer->status == 1 ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection