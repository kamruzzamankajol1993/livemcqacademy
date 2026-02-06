<form action="{{ route('exam-setup.store') }}" method="POST" class="modal-content">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">New Exam Configuration</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="fw-bold">Exam Categories (Multiple) <span class="text-danger">*</span></label>
                <select name="exam_category_ids[]" class="form-control select2" multiple required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name_en }} ({{ $cat->name_bn }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold">Total Questions</label>
                <input type="number" name="total_questions" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold">Mark per Question</label>
                <input type="number" step="0.01" name="per_question_mark" class="form-control" value="1.0" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="fw-bold">Negative Marking (Multiple)</label>
                <select name="negative_marks[]" class="form-control select2" multiple required>
                    <option value="0">0</option>
                    <option value="0.20">0.20</option>
                    <option value="0.25">0.25</option>
                    <option value="0.50">0.50</option>
                    <option value="1.00">1.00</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="fw-bold">Pass Mark</label>
                <input type="number" step="0.01" name="pass_mark" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="fw-bold">Duration (Minutes)</label>
                <input type="number" name="exam_duration_minutes" class="form-control" required>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Setup</button>
    </div>
</form>