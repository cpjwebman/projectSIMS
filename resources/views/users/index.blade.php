<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
                <p class="mb-1 small fw-semibold text-danger text-uppercase">Administrator</p>
                <h1 class="h3 mb-0 fw-bold text-body">User Management</h1>
            </div>
            <p class="mb-0 small text-secondary">{{ number_format($users->total()) }} registered users</p>
        </div>
    </x-slot>

    <div class="container-fluid px-3 px-lg-4 py-4">
        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center gap-2" role="status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>{{ session('status') }}</span></div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="card border-0 shadow-sm">
            <div class="card-body border-bottom">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                    <form method="GET" action="{{ route('users.index') }}" class="row flex-grow-1 g-2 align-items-end">
                        <div class="col-12 col-md">
                            <label for="search" class="form-label small fw-semibold">Search users</label>
                            <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Name, email, role, or status" class="form-control">
                        </div>
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1" aria-hidden="true"></i>Search</button>
                            @if ($search !== '')
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Clear</a>
                            @endif
                        </div>
                    </form>
                    <p class="small text-secondary mb-1">New users register through the account registration page.</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th scope="col" class="ps-3">#</th><th scope="col">User</th><th scope="col">Status</th><th scope="col">Access</th><th scope="col" class="pe-3 text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="ps-3 text-secondary">{{ $users->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $user->name }} @if ($user->is(auth()->user())) <span class="badge text-bg-light border">(you)</span> @endif</div>
                                    <div class="small text-secondary">{{ $user->email }}</div>
                                </td>
                                <td><span class="badge rounded-pill text-bg-{{ $user->is_active ? 'success' : 'warning' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td><span class="badge text-bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'student' ? 'info' : 'secondary') }}">{{ match ($user->role) { 'admin' => 'Administrator', 'student' => 'Student', default => 'Staff' } }}</span></td>
                                <td class="pe-3">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#user-details-{{ $user->id }}" aria-expanded="false" aria-controls="user-details-{{ $user->id }}"><i class="bi bi-eye me-1" aria-hidden="true"></i>View</button>
                                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#user-edit-{{ $user->id }}" aria-expanded="false" aria-controls="user-edit-{{ $user->id }}"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Edit</button>
                                        @unless ($user->is(auth()->user()))
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1" aria-hidden="true"></i>Delete</button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" class="p-0 border-0">
                                    <div class="collapse bg-body-tertiary px-4 py-3" id="user-details-{{ $user->id }}">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-3">Name</dt><dd class="col-sm-9">{{ $user->name }}</dd>
                                        <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $user->email }}</dd>
                                        <dt class="col-sm-3">Role / status</dt><dd class="col-sm-9">{{ match ($user->role) { 'admin' => 'Administrator', 'student' => 'Student', default => 'Staff' } }} / {{ $user->is_active ? 'Active' : 'Inactive' }}</dd>
                                        <dt class="col-sm-3">Registered</dt><dd class="col-sm-9 mb-0">{{ $user->created_at?->format('M j, Y') ?: '—' }}</dd>
                                    </dl>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" class="p-0 border-0">
                                    <div class="collapse bg-body-tertiary px-4 py-3" id="user-edit-{{ $user->id }}">
                                    <form method="POST" action="{{ route('users.update', $user) }}" class="row g-3 align-items-end">
                                        @csrf
                                        @method('PATCH')
                                        <div class="col-12 col-sm-5 col-lg-4">
                                            <label class="form-label small fw-semibold" for="role-{{ $user->id }}">Role</label>
                                            <select id="role-{{ $user->id }}" name="role" class="form-select">
                                                <option value="staff" @selected($user->role === 'staff')>Staff</option>
                                                <option value="student" @selected($user->role === 'student')>Student</option>
                                                <option value="admin" @selected($user->role === 'admin')>Administrator</option>
                                            </select>   
                                        </div>
                                        <div class="col-12 col-sm-4 col-lg-3">
                                            <label class="form-label small fw-semibold" for="active-{{ $user->id }}">Account status</label>
                                            <select id="active-{{ $user->id }}" name="is_active" class="form-select">
                                                <option value="1" @selected($user->is_active)>Active</option>
                                                <option value="0" @selected(! $user->is_active)>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-auto">
                                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>Save changes</button>
                                        </div>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-5 text-center text-secondary"><i class="bi bi-people fs-3 d-block mb-2" aria-hidden="true"></i>No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="card-footer bg-white border-0">{{ $users->links('pagination::bootstrap-5') }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
