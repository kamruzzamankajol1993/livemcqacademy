@extends('admin.master.master')

@section('title') Create Package | {{ $ins_name ?? 'App' }} @endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Create Subscription Package</h2>
            <a href="{{ route('package.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <form action="{{ route('package.store') }}" method="POST">
            @csrf
            <div class="row">
                {{-- Package Basic Details --}}
                <div class="col-md-7">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 text-primary"><i class="fa fa-info-circle me-2"></i>Package Information</h5>
                        </div>
                        <div class="card-body">
                            @include('flash_message')
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Package Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required placeholder="e.g. S-BASIC">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Plan Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-control" required>
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Original Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="original_price" class="form-control" required placeholder="0.00">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Sale Price (Current) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="price" class="form-control" required placeholder="0.00">
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_popular" id="isPopular" value="1">
                                        <label class="form-check-label fw-bold" for="isPopular">Mark as Popular / Best Seller</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Feature Selection Card --}}
                <div class="col-md-5">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary"><i class="fa fa-list-check me-2"></i>Select Features</h5>
                        </div>
                        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                            <p class="small text-muted mb-3">Check the feature and enter its specific limit/value for this package.</p>
                            
                            @foreach($features as $feature)
                            <div class="feature-item p-2 mb-2 border rounded bg-light">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input feature-checkbox" type="checkbox" 
                                                   name="features[{{ $feature->id }}][selected]" 
                                                   id="feat_{{ $feature->id }}" value="1">
                                            <label class="form-check-label fw-bold" for="feat_{{ $feature->id }}">
                                                {{ $feature->name }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="features[{{ $feature->id }}][value]" 
                                               class="form-control form-control-sm feature-value" 
                                               placeholder="Value (e.g. 50, Unlimited, Yes)" disabled>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="card-footer bg-light text-end">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fa fa-save me-1"></i> Save Package
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Toggle input field based on checkbox selection
        $('.feature-checkbox').on('change', function() {
            let valueInput = $(this).closest('.feature-item').find('.feature-value');
            if($(this).is(':checked')) {
                valueInput.prop('disabled', false).focus();
            } else {
                valueInput.prop('disabled', true).val('');
            }
        });
    });
</script>
@endsection