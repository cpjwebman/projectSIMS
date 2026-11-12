<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="name" class="form-label fw-semibold">Year level name</label>
        <input id="name" name="name" type="text" maxlength="255" value="{{ old('name', $yearLevel?->name) }}" required class="form-control @error('name') is-invalid @enderror">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="year_level" class="form-label fw-semibold">Level (numeric order)</label>
        <input id="year_level" name="year_level" type="number" min="1" max="255" step="1" value="{{ old('year_level', $yearLevel?->year_level) }}" required class="form-control @error('year_level') is-invalid @enderror">
        @error('year_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="status" class="form-label fw-semibold">Status</label>
        <select id="status" name="status" required class="form-select @error('status') is-invalid @enderror">
            <option value="active" @selected(old('status', $yearLevel?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $yearLevel?->status) === 'inactive')>Inactive</option>
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 border-top pt-3">
    <a href="{{ route('year-levels.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>