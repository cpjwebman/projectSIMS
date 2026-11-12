@include('auth.partials.auth-page-styles')

@push('scripts')
    <script>
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('password-toggle');

        passwordToggle.addEventListener('click', () => {
            const showPassword = passwordInput.type === 'password';
            passwordInput.type = showPassword ? 'text' : 'password';
            passwordToggle.setAttribute('aria-pressed', String(showPassword));
            passwordToggle.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
            passwordToggle.innerHTML = `<i class="bi ${showPassword ? 'bi-eye-slash' : 'bi-eye'}" aria-hidden="true"></i>`;
        });

        document.getElementById('login-form').addEventListener('submit', () => {
            const submitButton = document.getElementById('login-submit');
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
                    <p class="mt-3 mb-0">A connected campus starts here. Sign in to manage your learning community.</p>
                </div>
                <div class="login-brand-note"><i class="bi bi-shield-check me-2" aria-hidden="true"></i>Secure access to your school portal</div>
            </section>

            <section class="col-md-7 login-form-panel" aria-labelledby="login-heading">
                <div class="mb-4">
                    <p class="small fw-semibold text-uppercase text-primary mb-2">Account access</p>
                    <h2 id="login-heading" class="login-title mb-2">Sign in to your account</h2>
                    <p class="login-subtitle mb-0">Enter your credentials to continue.</p>
                </div>

                <x-auth-session-status class="alert alert-success py-2 mb-4" :status="session('status')" />

                <form id="login-form" method="POST" action="{{ route('login') }}">
                    @csrf

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
                        <label for="password" class="form-label login-label fw-semibold">Password</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-lock"></i></span>
                            <input
                                id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                type="password"
                                name="password"
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                                aria-describedby="password-error"
                                @error('password') aria-invalid="true" @enderror
                            >
                            <button
                                id="password-toggle"
                                class="btn login-password-toggle"
                                type="button"
                                aria-label="Show password"
                                aria-pressed="false"
                                aria-controls="password"
                            ><i class="bi bi-eye" aria-hidden="true"></i></button>
                        </div>
                        @error('password')
                            <div id="password-error" class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                        <div class="form-check">
                            <input id="remember_me" type="checkbox" class="form-check-input" name="remember" value="1" @checked(old('remember'))>
                            <label for="remember_me" class="form-check-label text-secondary">Remember me</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="small text-decoration-none" href="{{ route('password.request') }}">Forgot password?</a>
                        @endif
                    </div>

                    <button id="login-submit" class="btn btn-primary login-submit w-100" type="submit">
                        <span class="login-button-label"><i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>Sign in</span>
                        <span class="login-button-loading d-none" aria-live="polite"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Signing in...</span>
                    </button>
                </form>

                @if (Route::has('register'))
                    <p class="login-links text-center text-secondary small mt-4 mb-0">
                        New to the system?
                        <a href="{{ route('register') }}">Create an account</a>
                    </p>
                @endif
            </section>
        </div>
    </div>
</x-guest-layout>
