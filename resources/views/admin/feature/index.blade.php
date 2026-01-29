@extends('admin.master.master')

@section('title')
Feature Management | {{ $ins_name ?? 'App' }}
@endsection

@section('css')
{{-- jQuery UI CSS (Optional for styling helper) --}}
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<style>
    .color-preview-box { width: 100%; height: 40px; border-radius: 5px; border: 1px solid #ddd; margin-top: 5px; background-color: #ddd; }
    .table-color-preview { width: 30px; height: 30px; border-radius: 50%; border: 1px solid #ccc; display: inline-block; vertical-align: middle; }
    th.sortable { cursor: pointer; background-color: #f8f9fa; }
    th.sortable:hover { background-color: #e2e6ea; }

    /* Sortable List Styles */
    #sortable-list { list-style-type: none; margin: 0; padding: 0; }
    #sortable-list li { 
        margin: 5px 0; 
        padding: 10px 15px; 
        background: #fff; 
        border: 1px solid #ddd; 
        border-radius: 4px; 
        cursor: grab; 
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    #sortable-list li:hover { background-color: #f1f1f1; }
    .ui-state-highlight { height: 50px; line-height: 50px; background-color: #fdfdfd; border: 1px dashed #ccc; margin-bottom: 5px; border-radius: 4px; }
</style>
<style>
    .color-preview-box {
        width: 100%;
        height: 40px;
        border-radius: 5px;
        border: 1px solid #ddd;
        margin-top: 5px;
        background-color: #ddd; /* Default */
    }
    .table-color-preview {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 1px solid #ccc;
        display: inline-block;
        vertical-align: middle;
    }
    th.sortable {
        cursor: pointer;
        background-color: #f8f9fa;
    }
    th.sortable:hover {
        background-color: #e2e6ea;
    }
</style>
@endsection

@section('body')
<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h2 class="mb-0">Feature List</h2>
            <div class="d-flex align-items-center">
                <button type="button" data-bs-toggle="modal" data-bs-target="#addModal" class="btn btn-primary text-white">
                    <i class="fa fa-plus me-1"></i> Add New Feature
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white border-bottom-0">
                <ul class="nav nav-tabs card-header-tabs" id="featureTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#listView" type="button" role="tab">
                            <i class="fa fa-list me-1"></i> List View
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sort-tab" data-bs-toggle="tab" data-bs-target="#sortView" type="button" role="tab">
                            <i class="fa fa-sort me-1"></i> Drag & Drop Sort
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                @include('flash_message')
                
                <div class="tab-content" id="featureTabContent">
                    
                    <div class="tab-pane fade show active" id="listView" role="tabpanel">
                        <div class="d-flex justify-content-end mb-3">
                            <input class="form-control" id="searchInput" type="search" placeholder="Search features..." style="max-width: 250px;">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Sl</th>
                                        <th>Icon</th>
                                        <th class="sortable" data-column="english_name">English Name</th>
                                        <th class="sortable" data-column="bangla_name">Bangla Name</th>
                                        <th>Color</th>
                                        <th class="sortable" data-column="status">Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted"></div>
                            <nav><ul class="pagination justify-content-center mb-0" id="pagination"></ul></nav>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="sortView" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle me-1"></i> Drag items to reorder and the changes will be saved automatically.
                        </div>
                        <ul id="sortable-list">
                            @foreach($allFeatures as $item)
                                <li data-id="{{ $item->id }}">
                                    <div class="d-flex align-items-center gap-3">
                                        <i class="fa fa-bars text-muted me-2"></i>
                                        @if($item->image)
                                            <img src="{{ asset('public/'.$item->image) }}" width="40" height="40" class="rounded">
                                        @else
                                            <span class="badge bg-secondary">No Img</span>
                                        @endif
                                        <div>
                                            <strong>{{ $item->english_name }}</strong> <br>
                                            <small class="text-muted">{{ $item->bangla_name }}</small>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="table-color-preview" style="background: {{ $item->color }}; width: 20px; height: 20px;"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>
@include('admin.feature._partial.addModal')
@include('admin.feature._partial.editModal')
@endsection

@section('script')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
@include('admin.feature._partial.script')
@endsection