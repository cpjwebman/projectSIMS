<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="mb-1 small fw-semibold text-primary text-uppercase">Academic Management</p>
            <h1 class="h3 mb-0 fw-bold text-body">Add Subject</h1>
        </div>
    </x-slot>

    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="mx-auto" style="max-width: 52rem;">
            <form method="POST" action="{{ route('subjects.store') }}" class="card border-0 shadow-sm">
                <div class="card-body d-grid gap-4">
                @csrf
                @include('subjects._form', ['subject' => null, 'submitLabel' => 'Add Subject'])
                </div>
            </form>
        </div>
    </div>
</x-app-layout>