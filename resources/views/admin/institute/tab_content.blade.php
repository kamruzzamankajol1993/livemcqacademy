<ul class="nav nav-pills mb-3" id="pills-tab-{{ $type }}" role="tablist">
    <li class="nav-item">
        <button class="nav-link active btn-sm" data-bs-toggle="pill" data-bs-target="#table-view-{{ $type }}">List View</button>
    </li>
    <li class="nav-item">
        <button class="nav-link btn-sm" data-bs-toggle="pill" data-bs-target="#sort-view-{{ $type }}">Drag & Drop Sort</button>
    </li>
</ul>

<div class="tab-content">
    {{-- Table View --}}
    <div class="tab-pane fade show active" id="table-view-{{ $type }}">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Sl</th>
                        <th>Name (EN)</th>
                        <th>Name (BN)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody_{{ $type }}"></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted" id="info_{{ $type }}"></div>
            <nav><ul class="pagination justify-content-center mb-0" id="pagination_{{ $type }}"></ul></nav>
        </div>
    </div>

    {{-- Sort View --}}
    <div class="tab-pane fade" id="sort-view-{{ $type }}">
        <div class="alert alert-info"><i class="fa fa-info-circle me-1"></i> Drag items to reorder {{ ucfirst($type) }}s.</div>
        <ul class="sortable-list" id="sortable-{{ $type }}">
            @foreach($items as $item)
            <li data-id="{{ $item->id }}">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa fa-bars text-muted"></i>
                    <strong>{{ $item->name_en }}</strong> ({{ $item->name_bn }})
                </div>
            </li>
            @endforeach
        </ul>
    </div>
</div>