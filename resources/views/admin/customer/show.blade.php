@extends('admin.master.master')
@section('title', 'Student Profile | ' . $customer->name)

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Student Profile</h2>
            <a href="{{ route('student.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <div class="row">
            {{-- বাম পাশ: প্রোফাইল এবং প্যাকেজ অ্যাসাইন ফর্ম --}}
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <i class="fa fa-user-circle fa-5x text-muted mb-3"></i>
                        <h4 class="mb-1">{{ $customer->name }}</h4>
                        <p class="text-muted small mb-3">{{ $customer->email }}<br>{{ $customer->phone }}</p>
                        
                        <div class="p-2 border rounded bg-light">
                            <h6 class="mb-1">Active Subscription</h6>
                            @php
                                $activeSub = $customer->user->activeSubscription;
                                // বর্তমান সময় (Bangladesh Standard Time)
                                $now = \Carbon\Carbon::now('Asia/Dhaka');
                            @endphp

                            @if($activeSub && $activeSub->end_date > $now)
                                <span class="badge bg-success mb-1">{{ $activeSub->package->name }}</span>
                                <p class="small text-danger mb-0">Expires: {{ $activeSub->end_date->format('d M, Y') }}</p>
                            @else
                                <span class="badge bg-danger">No Active Plan</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- প্যাকেজ অ্যাসাইন ফর্ম --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="mb-0"><i class="fa fa-plus-circle me-1"></i> Assign / Renew Package</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('student.assignPackage', $customer->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select Package</label>
                                <select name="package_id" class="form-select" required>
                                    @foreach($packages as $pkg)
                                        <option value="{{ $pkg->id }}">{{ $pkg->name }} - {{ $pkg->price }} TK</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Amount Paid (TK)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="Cash">Cash</option>
                                    <option value="Bkash">Bkash</option>
                                    <option value="Nagad">Nagad</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Activate Package</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ডান পাশ: হিস্ট্রি টেবিল --}}
            <div class="col-md-8">
                @include('flash_message')
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#pay-content">Payment History</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sub-content">Subscription Logs</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            {{-- পেমেন্ট হিস্ট্রি --}}
                            <div class="tab-pane fade show active" id="pay-content">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date (BST)</th>
                                            <th>Package</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->user->payments->sortByDesc('created_at') as $payment)
                                        <tr>
                                            <td>{{ $payment->created_at->timezone('Asia/Dhaka')->format('d M, Y h:i A') }}</td>
                                            <td>{{ $payment->package->name }}</td>
                                            <td>{{ $payment->amount }} TK</td>
                                            <td>{{ $payment->payment_method }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- সাবস্ক্রিপশন লগ: এখানে IF কন্ডিশন দিয়ে চেক করা হয়েছে --}}
                            <div class="tab-pane fade" id="sub-content">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Package</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->user->subscriptions->sortByDesc('created_at') as $sub)
                                        <tr>
                                            <td><strong>{{ $sub->package->name }}</strong></td>
                                            <td>{{ $sub->start_date->format('d M, Y') }}</td>
                                            <td>{{ $sub->end_date->format('d M, Y') }}</td>
                                            <td>
                                                {{-- ডেট চেক কন্ডিশন --}}
                                                @if($sub->end_date < $now)
                                                    <span class="badge bg-danger">Expired</span>
                                                @elseif($sub->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($sub->status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection