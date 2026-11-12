@include('auth.partials.auth-page-styles')

@push('scripts')
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const password = document.getElementById(toggle.dataset.passwordToggle);
                const showPassword = password.type === 'password';

                password.type = showPassword ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(showPassword));
                toggle.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
                toggle.innerHTML = `<i class="bi ${showPassword ? 'bi-eye-slash' : 'bi-eye'}" aria-hidden="true"></i>`;
            });
        });
    </script>
@endpush

<x-guest-layout>
    <div class="login-card">
        <div class="row g-0">
            <section class="col-md-5 login-brand-panel" aria-label="School system introduction">
                <div class="login-brand-content">
                    <span class="login-school-icon" aria-hidden="true"><i class="bi bi-mortarboard-fill"></i></span>
                    <p class="mb-2 small fw-semibold text-uppercase" style="color: #e6b85c; letter-spacing: .12em;">Welcome to</p>
                    <h1>Student InformationManagement System</h1>
                    <p class="mt-3 mb-0">A connected campus starts here. Create your account to join the learning community.</p>
                </div>
                <div class="login-brand-note"><i class="bi bi-shield-check me-2" aria-hidden="true"></i>Secure access to your school portal</div>
            </section>

            <section class="col-md-7 login-form-panel" aria-labelledby="register-heading">
                <div class="mb-4">
                    <p class="small fw-semibold text-uppercase text-primary mb-2">Student account</p>
                    <h2 id="register-heading" class="login-title mb-2">Create your account</h2>
                    <p class="login-subtitle mb-0">Enter your details to get started.</p>
                </div>

                <form id="register-form" method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label login-label fw-semibold">Full name</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-person"></i></span>
                            <input
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Your full name"
                                required
                                autofocus
                                autocomplete="name"
                                aria-describedby="name-error"
                                @error('name') aria-invalid="true" @enderror
                            >
                        </div>
                        @error('name')
                            <div id="name-error" class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label login-label fw-semibold">Email address</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                            <input
                                id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="you@school.edu"
                                required
                                autocomplete="username"
                                aria-describedby="email-error"
                                @error('email') aria-invalid="true" @enderror
                            >
                        </div>
                        @error('email')
                            <div id="email-error" class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label login-label fw-semibold">Password</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-lock"></i></span>
                            <input
                                id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                type="password"
                                name="password"
                                placeholder="Create a password"
                                required
                                autocomplete="new-password"
                                aria-describedby="password-error"
                                @error('password') aria-invalid="true" @enderror
                            >
                            <button
                                class="btn login-password-toggle"
                                type="button"
                                data-password-toggle="password"
                                aria-label="Show password"
                                aria-pressed="false"
                                aria-controls="password"
                            ><i class="bi bi-eye" aria-hidden="true"></i></button>
                        </div>
                        @error('password')
                            <div id="password-error" class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label login-label fw-semibold">Confirm password</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-lock-fill"></i></span>
                            <input
                                id="password_confirmation"
                                class="form-control"
                                type="password"
                                name="password_confirmation"
                                placeholder="Re-enter your password"
                                required
                                autocomplete="new-password"
                                aria-describedby="password-confirmation-error"
                            >
                            <button
                                class="btn login-password-toggle"
                                type="button"
                                data-password-toggle="password_confirmation"
                                aria-label="Show confirmation password"
                                aria-pressed="false"
                                aria-controls="password_confirmation"
                            ><i class="bi bi-eye" aria-hidden="true"></i></button>
                        </div>
                        @error('password_confirmation')
                            <div id="password-confirmation-error" class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button id="register-submit" class="btn btn-primary login-submit w-100" type="submit">
                        <i class="bi bi-person-plus me-2" aria-hidden="true"></i>Create Account
                    </button>
                </form>

                <p class="login-links text-center text-secondary small mt-4 mb-0">
                    Already have an account?
                    <a href="{{ route('login') }}">Login</a>
                </p>
            </section>
        </div>
    </div>
</x-guest-layout>
