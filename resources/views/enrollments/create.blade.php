<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="mb-1 small fw-semibold text-primary text-uppercase">Enrollment Management</p>
            <h1 class="h3 mb-0 fw-bold text-body">Create Enrollment</h1>
        </div>
    </x-slot>

    <div class="container-fluid px-3 px-lg-4 py-4">
        <form method="POST" action="{{ route('enrollments.store') }}" class="card border-0 shadow-sm mx-auto" style="max-width: 64rem;">
            @csrf
            <div class="card-body d-grid gap-4 p-4 p-lg-5">
                @include('enrollments._form', ['enrollment' => null, 'submitLabel' => 'Create Enrollment'])
            </div>
        </form>
    </div>
</x-app-layout>
