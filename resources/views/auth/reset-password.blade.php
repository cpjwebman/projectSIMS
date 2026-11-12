@include('auth.partials.auth-page-styles')

@push('scripts')
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const input = document.getElementById(toggle.getAttribute('aria-controls'));
                const showPassword = input.type === 'password';

                input.type = showPassword ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(showPassword));
                toggle.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
                toggle.innerHTML = `<i class="bi ${showPassword ? 'bi-eye-slash' : 'bi-eye'}" aria-hidden="true"></i>`;
            });
        });

        document.getElementById('reset-password-form').addEventListener('submit', () => {
            const submitButton = document.getElementById('reset-password-submit');
            submitButton.disabled = true;
            submitButton.querySelector('.login-button-label').classList.add('d-none');
            submitButton.querySelector('.login-button-loading').classList.remove('d-none');
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
                    <h1>Student Information Management System (SIMS)</h1>
                    <p class="mt-3 mb-0">A connected campus starts here. Choose a new password to secure your account.</p>
                </div>
                <div class="login-brand-note"><i class="bi bi-shield-check me-2" aria-hidden="true"></i>Secure access to your school portal</div>
            </section>

            <section class="col-md-7 login-form-panel" aria-labelledby="reset-password-heading">
                <div class="mb-4">
                    <p class="small fw-semibold text-uppercase text-primary mb-2">Account recovery</p>
                    <h2 id="reset-password-heading" class="login-title mb-2">Reset your password</h2>
                    <p class="login-subtitle mb-0">Enter your email address and choose a new password.</p>
                </div>

                <x-auth-session-status class="alert alert-success py-2 mb-4" :status="session('status')" />

                <form id="reset-password-form" method="POST" action="{{ route('password.store') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="mb-3">
                        <label for="email" class="form-label login-label fw-semibold">Email address</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                            <input
                                id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                type="email"
                                name="email"
                                value="{{ old('email', $request->email) }}"
                                placeholder="you@school.edu"
                                required
                                autofocus
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
                        <label for="password" class="form-label login-label fw-semibold">New password</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-lock"></i></span>
                            <input
                                id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                type="password"
                                name="password"
                                placeholder="Enter a new password"
                                required
                                autocomplete="new-password"
                                aria-describedby="password-error"
                                @error('password') aria-invalid="true" @enderror
                            >
                            <button
                                class="btn login-password-toggle"
                                type="button"
                                data-password-toggle
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
                        <label for="password_confirmation" class="form-label login-label fw-semibold">Confirm new password</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-lock-fill"></i></span>
                            <input
                                id="password_confirmation"
                                class="form-control"
                                type="password"
                                name="password_confirmation"
                                placeholder="Re-enter your new password"
                                required
                                autocomplete="new-password"
                                aria-describedby="password_confirmation-error"
                            >
                            <button
                                class="btn login-password-toggle"
                                type="button"
                                data-password-toggle
                                aria-label="Show password"
                                aria-pressed="false"
                                aria-controls="password_confirmation"
                            ><i class="bi bi-eye" aria-hidden="true"></i></button>
                        </div>
                        @error('password_confirmation')
                            <div id="password_confirmation-error" class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button id="reset-password-submit" class="btn btn-primary login-submit w-100" type="submit">
                        <span class="login-button-label"><i class="bi bi-shield-lock me-2" aria-hidden="true"></i>Reset Password</span>
                        <span class="login-button-loading d-none" aria-live="polite"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Resetting password...</span>
                    </button>
                </form>

                <p class="login-links text-center text-secondary small mt-4 mb-0">
                    Remember your password?
                    <a href="{{ route('login') }}">Back to Login</a>
                </p>
            </section>
        </div>
    </div>
</x-guest-layout>
