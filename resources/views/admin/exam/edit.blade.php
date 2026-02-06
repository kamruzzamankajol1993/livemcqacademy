<form action="{{ route('exam-setup.update', $exam->id) }}" method="POST" class="modal-content">
    @csrf @method('PUT')
    <div class="modal-header bg-info text-white">
        <h5 class="modal-title">Edit Exam Configuration</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="fw-bold">Exam Categories (Multiple) <span class="text-danger">*</span></label>
                <select name="exam_category_ids[]" class="form-control select2" multiple required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ in_array($cat->id, (array)$exam->exam_category_ids) ? 'selected' : '' }}>
                            {{ $cat->name_en }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold">Total Questions</label>
                <input type="number" name="total_questions" class="form-control" value="{{ $exam->total_questions }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="fw-bold">Mark per Question</label>
                <input type="number" step="0.01" name="per_question_mark" class="form-control" value="{{ $exam->per_question_mark }}" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="fw-bold">Negative Marking (Multiple)</label>
                <select name="negative_marks[]" class="form-control select2" multiple required>
                    @foreach(['0','0.20','0.25','0.50','1.00'] as $val)
                        <option value="{{ $val }}" {{ in_array($val, (array)$exam->negative_marks) ? 'selected' : '' }}>{{ $val }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="fw-bold">Pass Mark</label>
                <input type="number" step="0.01" name="pass_mark" class="form-control" value="{{ $exam->pass_mark }}" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="fw-bold">Duration (Minutes)</label>
                <input type="number" name="exam_duration_minutes" class="form-control" value="{{ $exam->exam_duration_minutes }}" required>
            </div>
            <div class="col-md-12 mb-3">
                <label class="fw-bold">Status</label>
                <select name="status" class="form-control">
                    <option value="1" {{ $exam->status == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $exam->status == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-info text-white">Update Setup</button>
    </div>
</form>