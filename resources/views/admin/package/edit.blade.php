@extends('admin.master.master')

@section('title') Edit Package | {{ $ins_name ?? 'App' }} @endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Edit Package: {{ $package->name }}</h2>
            <a href="{{ route('package.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <form action="{{ route('package.update', $package->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                {{-- Package Basic Details --}}
                <div class="col-md-7">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 text-primary"><i class="fa fa-edit me-2"></i>Package Information</h5>
                        </div>
                        <div class="card-body">
                            @include('flash_message')
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Package Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ $package->name }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Plan Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-control" required>
                                        <option value="monthly" {{ $package->type == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="yearly" {{ $package->type == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="1" {{ $package->status == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ $package->status == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Original Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="original_price" class="form-control" value="{{ $package->original_price }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Sale Price (Current) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="price" class="form-control" value="{{ $package->price }}" required>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_popular" id="isPopular" value="1" {{ $package->is_popular ? 'checked' : '' }}>
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
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 text-primary"><i class="fa fa-list-check me-2"></i>Select Features</h5>
                        </div>
                        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                            @foreach($features as $feature)
                            @php
                                $isChecked = isset($selectedFeatures[$feature->id]);
                                $pivotValue = $isChecked ? $selectedFeatures[$feature->id] : '';
                            @endphp
                            <div class="feature-item p-2 mb-2 border rounded {{ $isChecked ? 'bg-white border-primary' : 'bg-light' }}">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input feature-checkbox" type="checkbox" 
                                                   name="features[{{ $feature->id }}][selected]" 
                                                   id="feat_{{ $feature->id }}" value="1" {{ $isChecked ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="feat_{{ $feature->id }}">
                                                {{ $feature->name }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="features[{{ $feature->id }}][value]" 
                                               class="form-control form-control-sm feature-value" 
                                               placeholder="Limit (e.g. Unlimited)" 
                                               value="{{ $pivotValue }}"
                                               {{ $isChecked ? '' : 'disabled' }}>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="card-footer bg-light text-end">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fa fa-sync me-1"></i> Update Package
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
            let container = $(this).closest('.feature-item');
            let valueInput = container.find('.feature-value');
            if($(this).is(':checked')) {
                valueInput.prop('disabled', false).focus();
                container.removeClass('bg-light').addClass('bg-white border-primary');
            } else {
                valueInput.prop('disabled', true).val('');
                container.removeClass('bg-white border-primary').addClass('bg-light');
            }
        });
    });
</script>
@endsection