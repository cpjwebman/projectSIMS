<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="course_code" class="form-label fw-semibold">Course code</label>
        <input id="course_code" name="course_code" type="text" value="{{ old('course_code', $course?->course_code) }}" required maxlength="255" class="form-control @error('course_code') is-invalid @enderror">
        @error('course_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="name" class="form-label fw-semibold">Course name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $course?->name) }}" required maxlength="255" class="form-control @error('name') is-invalid @enderror">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="status" class="form-label fw-semibold">Status</label>
        <select id="status" name="status" required class="form-select @error('status') is-invalid @enderror">
            <option value="active" @selected(old('status', $course?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $course?->status) === 'inactive')>Inactive</option>
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label fw-semibold">Description</label>
        <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $course?->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 border-top pt-3">
    <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>