@include('auth.partials.auth-page-styles')

<x-guest-layout>
    <div class="login-card">
        <div class="row g-0">
            <section class="col-md-5 login-brand-panel" aria-label="School system introduction">
                <div class="login-brand-content">
                    <span class="login-school-icon" aria-hidden="true"><i class="bi bi-mortarboard-fill"></i></span>
                    <p class="mb-2 small fw-semibold text-uppercase" style="color: #e6b85c; letter-spacing: .12em;">Welcome to</p>
                    <h1>Student Information Management System (SIMS)</h1>
                    <p class="mt-3 mb-0">A connected campus starts here. Reset your password to get back to your learning community.</p>
                </div>
                <div class="login-brand-note"><i class="bi bi-shield-check me-2" aria-hidden="true"></i>Secure access to your school portal</div>
            </section>

            <section class="col-md-7 login-form-panel" aria-labelledby="forgot-password-heading">
                <div class="mb-4">
                    <p class="small fw-semibold text-uppercase text-primary mb-2">Account recovery</p>
                    <h2 id="forgot-password-heading" class="login-title mb-2">Forgot your password?</h2>
                    <p class="login-subtitle mb-0">Enter your email address and we’ll send you a link to reset your password.</p>
                </div>

                <x-auth-session-status class="alert alert-success py-2 mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label login-label fw-semibold">Email Address</label>
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

                    <button class="btn btn-primary login-submit w-100" type="submit">
                        <i class="bi bi-envelope-arrow-up me-2" aria-hidden="true"></i>Send Password Reset Link
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
