<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="mb-1 small fw-semibold text-primary text-uppercase">Academic Management</p>
            <h1 class="h3 mb-0 fw-bold text-body">Edit Subject</h1>
        </div>
    </x-slot>

    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="mx-auto" style="max-width: 52rem;">
            <form method="POST" action="{{ route('subjects.update', $subject) }}" class="card border-0 shadow-sm">
                <div class="card-body d-grid gap-4">
                @csrf
                @method('PUT')
                @include('subjects._form', ['subject' => $subject, 'submitLabel' => 'Save Changes'])
                </div>
            </form>
        </div>
    </div>
</x-app-layout>