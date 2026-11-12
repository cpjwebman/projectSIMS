<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="subject_code" class="form-label fw-semibold">Subject code</label>
        <input id="subject_code" name="subject_code" type="text" value="{{ old('subject_code', $subject?->subject_code) }}" required maxlength="255" class="form-control @error('subject_code') is-invalid @enderror">
        @error('subject_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="name" class="form-label fw-semibold">Subject name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $subject?->name) }}" required maxlength="255" class="form-control @error('name') is-invalid @enderror">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="course_id" class="form-label fw-semibold">Course</label>
        <select id="course_id" name="course_id" required class="form-select @error('course_id') is-invalid @enderror">
            <option value="">Select a course</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected((string) old('course_id', $subject?->course_id) === (string) $course->id)>{{ $course->course_code }} — {{ $course->name }}{{ $course->status === 'inactive' ? ' (Inactive)' : '' }}</option>
            @endforeach
        </select>
        @error('course_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="year_level_id" class="form-label fw-semibold">Year level</label>
        <select id="year_level_id" name="year_level_id" required class="form-select @error('year_level_id') is-invalid @enderror">
            <option value="">Select a year level</option>
            @foreach ($yearLevels as $yearLevel)
                <option value="{{ $yearLevel->id }}" @selected((string) old('year_level_id', $subject?->year_level_id) === (string) $yearLevel->id)>Year {{ $yearLevel->year_level }} — {{ $yearLevel->name }}{{ $yearLevel->status === 'inactive' ? ' (Inactive)' : '' }}</option>
            @endforeach
        </select>
        @error('year_level_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="units" class="form-label fw-semibold">Units</label>
        <input id="units" name="units" type="number" min="1" max="255" step="1" value="{{ old('units', $subject?->units ?? 1) }}" required class="form-control @error('units') is-invalid @enderror">
        @error('units') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="status" class="form-label fw-semibold">Status</label>
        <select id="status" name="status" required class="form-select @error('status') is-invalid @enderror">
            <option value="active" @selected(old('status', $subject?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $subject?->status) === 'inactive')>Inactive</option>
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label fw-semibold">Description</label>
        <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $subject?->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 border-top pt-3">
    <a href="{{ route('subjects.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>