@extends('admin.master.master')
@section('title', 'Package Details: ' . $package->name)

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Package: {{ $package->name }}</h2>
            <a href="{{ route('package.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <div class="row">
            {{-- বাম পাশ: প্যাকেজ ডিটেইলস এবং ফিচার লিস্ট --}}
            <div class="col-md-4">
                {{-- প্যাকেজ ইনফো --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="mb-0">General Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr><th>Price:</th><td class="text-end fw-bold">{{ $package->price }} TK</td></tr>
                            <tr><th>Type:</th><td class="text-end text-capitalize">{{ $package->type }}</td></tr>
                            <tr><th>Status:</th><td class="text-end">{!! $package->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}</td></tr>
                        </table>
                    </div>
                </div>

                {{-- প্যাকেজ ফিচার লিস্ট --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white py-3">
                        <h6 class="mb-0">Included Features</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($package->features as $feature)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa fa-check-circle text-success me-2"></i>
                                        {{ $feature->name }}
                                    </div>
                                    <span class="badge bg-info text-dark">{{ $feature->pivot->value }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted">No features added.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            {{-- ডান পাশ: ইউজার লিস্ট সেকশন --}}
            <div class="col-md-8">
                {{-- ১ সপ্তাহের মধ্যে শেষ হবে এমন ইউজার লিস্ট --}}
                <div class="card shadow-sm mb-4 border-warning">
                    <div class="card-header bg-warning text-dark py-3">
                        <h6 class="mb-0 fw-bold"><i class="fa fa-clock me-1"></i> Expiring Soon (Within 7 Days)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Expire Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expiringSoon as $sub)
                                    <tr>
                                        <td><strong>{{ $sub->user->name }}</strong></td>
                                        <td>{{ $sub->user->phone }}</td>
                                        <td class="text-danger fw-bold">{{ $sub->end_date->format('d M, Y') }}</td>
                                        <td class="text-center">
                                            @if($sub->user->customer)
                                            <a href="{{ route('customer.show', $sub->user->customer->id) }}" class="btn btn-xs btn-outline-primary">View</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-3 text-muted small">No users expiring soon.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- অল একটিভ ইউজার লিস্ট --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 text-primary fw-bold">Active Users (Current)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Valid Till</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activeUsers as $sub)
                                    <tr>
                                        <td>{{ $sub->user->name }}</td>
                                        <td>{{ $sub->user->phone }}</td>
                                        <td>{{ $sub->end_date->format('d M, Y') }}</td>
                                        <td class="text-center">
                                            @if($sub->user->customer)
                                            <a href="{{ route('customer.show', $sub->user->customer->id) }}" class="btn btn-xs btn-primary shadow-sm"><i class="fa fa-eye"></i> Profile</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-3 text-muted">No active users for this package.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection