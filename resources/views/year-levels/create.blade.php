<x-app-layout>
    <x-slot name="header">
        <div><p class="mb-1 small fw-semibold text-primary text-uppercase">Academic Management</p><h1 class="h3 mb-0 fw-bold text-body">Add Year Level</h1></div>
    </x-slot>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <form method="POST" action="{{ route('year-levels.store') }}" class="card border-0 shadow-sm mx-auto" style="max-width: 52rem;">
            @csrf
            <div class="card-body d-grid gap-4 p-4 p-lg-5">@include('year-levels._form', ['yearLevel' => null, 'submitLabel' => 'Add Year Level'])</div>
        </form>
    </div>
</x-app-layout>
