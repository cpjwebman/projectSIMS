<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Add Student</h2>
    </x-slot>

    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <form method="POST" action="{{ route('students.store') }}" class="space-y-6 rounded-md border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800 sm:p-8">
                @csrf
                @include('students._form', ['student' => null, 'submitLabel' => 'Add Student'])
            </form>
        </div>
    </div>
</x-app-layout>