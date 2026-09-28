<label class="form-label fw-semibold">Department</label>
<select class="form-select mb-3" name="department_id" required>
    <option value="">Select department</option>
    @foreach ($departments as $department)
        <option value="{{ $department->id }}" @selected(old('department_id', $position?->department_id) == $department->id)>{{ $department->name }}</option>
    @endforeach
</select>

<label class="form-label fw-semibold">Section</label>
<select class="form-select mb-3" name="section_id">
    <option value="">Department position</option>
    @foreach ($sections as $section)
        <option value="{{ $section->id }}" @selected(old('section_id', $position?->section_id) == $section->id)>{{ $section->department->name }} - {{ $section->name }}</option>
    @endforeach
</select>

<label class="form-label fw-semibold">Position Title</label>
<input class="form-control mb-3" name="title" value="{{ old('title', $position?->title) }}" placeholder="Finance Manager" required>

<label class="form-label fw-semibold">Code</label>
<input class="form-control mb-3" name="code" value="{{ old('code', $position?->code) }}" placeholder="FIN-MGR">

<label class="form-label fw-semibold">Description</label>
<textarea class="form-control" name="description" rows="4" placeholder="Position purpose or notes">{{ old('description', $position?->description) }}</textarea>
