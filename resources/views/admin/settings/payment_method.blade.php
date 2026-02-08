@extends('admin.master.master')
@section('title', 'Payment Settings')

@section('body')
<main class="main-content">
    <div class="container-fluid mt-4">
        @include('flash_message')

        <div class="row">
            {{-- নতুন মেথড অ্যাড ফর্ম --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fa fa-plus-circle me-1"></i> Add New Method
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.paymentSettingsStore') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Provider Name</label>
                                <input type="text" name="provider" class="form-control" placeholder="Bkash, Nagad, Rocket..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Account Number</label>
                                <input type="text" name="number" class="form-control" placeholder="017XXXXXXXX" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Type</label>
                                <select name="type" class="form-control">
                                    <option value="Personal">Personal</option>
                                    <option value="Agent">Agent</option>
                                    <option value="Merchant">Merchant</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Instruction</label>
                                <textarea name="instruction" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Add Method</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- পেমেন্ট মেথড লিস্ট ও ম্যানেজমেন্ট --}}
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">
                        <i class="fa fa-list me-1"></i> Manage Methods & Instructions
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.paymentSettingsUpdate') }}" method="POST">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr class="small">
                                            <th>Provider</th>
                                            <th>Number</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($settings as $item)
                                        <tr class="{{ $item->status ? '' : 'table-secondary text-muted' }}">
                                            <td class="fw-bold">{{ ucfirst($item->provider) }}</td>
                                            <td>
                                                <input type="text" name="settings[{{ $item->id }}][number]" class="form-control form-control-sm" value="{{ $item->number }}">
                                            </td>
                                            <td>
                                                <select name="settings[{{ $item->id }}][type]" class="form-control form-control-sm">
                                                    <option value="Personal" {{ $item->type == 'Personal' ? 'selected' : '' }}>Personal</option>
                                                    <option value="Agent" {{ $item->type == 'Agent' ? 'selected' : '' }}>Agent</option>
                                                    <option value="Merchant" {{ $item->type == 'Merchant' ? 'selected' : '' }}>Merchant</option>
                                                </select>
                                                {{-- ইনস্ট্রাকশন হিডেন ফিল্ডে রাখা হয়েছে যাতে আপডেট মেথডে পাওয়া যায় --}}
                                                <input type="hidden" name="settings[{{ $item->id }}][instruction]" value="{{ $item->instruction }}">
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox" name="settings[{{ $item->id }}][status]" {{ $item->status ? 'checked' : '' }}>
                                                    <label class="small">{{ $item->status ? 'Active' : 'Inactive' }}</label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $item->id }}">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="text-center py-4">No payment methods found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($settings->count() > 0)
                            <div class="text-end mt-2">
                                <button type="submit" class="btn btn-success btn-sm px-4">Update All Settings</button>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- ডিলিট করার জন্য হিডেন ফর্ম --}}
<form id="delete-form" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).on('click', '.delete-btn', function() {
        let id = $(this).data('id');
        let url = "{{ url('payment-method-settings/delete') }}/" + id;
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This method will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = $('#delete-form');
                form.attr('action', url);
                form.submit();
            }
        });
    });
</script>
@endsection