<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
    crossorigin="anonymous"
    >
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        @stack('styles')
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <div class="flex min-h-[calc(100vh-4rem)] flex-col lg:flex-row">
                <aside class="border-b border-gray-200 bg-white px-4 py-5 dark:border-gray-700 dark:bg-gray-800 lg:w-64 lg:shrink-0 lg:border-b-0 lg:border-r lg:px-5">
                    @if (auth()->user()?->isStudent())
                        <a href="{{ route('student.dashboard') }}" class="mb-3 flex items-center gap-3 rounded-md px-3 py-2 font-semibold text-gray-900 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('student.dashboard') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                            <span class="flex h-8 w-8 items-center justify-center rounded bg-indigo-100 text-lg text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i></span>
                            <span>Student Portal</span>
                        </a>
                        <nav aria-label="Student portal" class="mb-4 ml-11">
                            <a href="{{ route('student.profile.edit') }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"><i class="bi bi-person-circle" aria-hidden="true"></i><span>My Profile</span></a>
                            <a href="{{ route('student.dashboard') }}#my-enrollments" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"><i class="bi bi-card-checklist" aria-hidden="true"></i><span>My Enrollments</span></a>
                        </nav>
                    @else
                    @if (auth()->user()?->isAdministrator())
                        <a href="{{ route('users.index') }}" class="mb-6 flex items-center gap-3 rounded-md px-3 py-2 font-semibold text-gray-900 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('users.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                            <span class="flex h-8 w-8 items-center justify-center rounded bg-red-100 text-lg text-red-700 dark:bg-red-900 dark:text-red-200"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                            <span>User Management</span>
                        </a>
                    @endif
                    <a href="{{ route('students.index') }}" class="mb-3 flex items-center gap-3 rounded-md px-3 py-2 font-semibold text-gray-900 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('students.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded bg-indigo-100 text-lg text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200"><i class="bi bi-mortarboard-fill" aria-hidden="true"></i></span>
                        <span>Student Management</span>
                    </a>

                    <a href="{{ route('subjects.index') }}" class="mb-3 mt-6 flex items-center gap-3 rounded-md px-3 py-2 font-semibold text-gray-900 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('subjects.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded bg-emerald-100 text-lg text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200"><i class="bi bi-book-half" aria-hidden="true"></i></span>
                        <span>Subject Management</span>
                    </a>
                    
                    <a href="{{ route('courses.index') }}" class="mb-3 mt-6 flex items-center gap-3 rounded-md px-3 py-2 font-semibold text-gray-900 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('courses.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded bg-amber-100 text-lg text-amber-700 dark:bg-amber-900 dark:text-amber-200"><i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i></span>
                        <span>Course Management</span>
                    </a>
                    
                    <a href="{{ route('enrollments.index') }}" class="mb-3 mt-6 flex items-center gap-3 rounded-md px-3 py-2 font-semibold text-gray-900 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('enrollments.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded bg-sky-100 text-lg text-sky-700 dark:bg-sky-900 dark:text-sky-200"><i class="bi bi-clipboard2-check-fill" aria-hidden="true"></i></span>
                        <span>Enrollment Management</span>
                    </a>
                    
                    <a href="{{ route('year-levels.index') }}" class="mb-3 mt-6 flex items-center gap-3 rounded-md px-3 py-2 font-semibold text-gray-900 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('year-levels.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <span class="flex h-8 w-8 items-center justify-center rounded bg-rose-100 text-lg text-rose-700 dark:bg-rose-900 dark:text-rose-200"><i class="bi bi-bar-chart-steps" aria-hidden="true"></i></span>
                        <span>Year Level Management</span>
                    </a>
                    
                    @endif
                </aside>

                <div class="min-w-0 flex-1">
                    @isset($header)
                        <header class="bg-white shadow dark:bg-gray-800">
                            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <main>
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
        <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"
    ></script>
        @stack('scripts')
    </body>
</html>
