<x-app-layout>
    <x-slot name="header">
        <div><p class="mb-1 small fw-semibold text-primary text-uppercase">Course Management</p><h1 class="h3 mb-0 fw-bold text-body">Edit Course</h1></div>
    </x-slot>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <form method="POST" action="{{ route('courses.update', $course) }}" class="card border-0 shadow-sm mx-auto" style="max-width: 52rem;">
            @csrf
            @method('PUT')
            <div class="card-body d-grid gap-4 p-4 p-lg-5">@include('courses._form', ['course' => $course, 'submitLabel' => 'Save Changes'])</div>
        </form>
    </div>
</x-app-layout>
