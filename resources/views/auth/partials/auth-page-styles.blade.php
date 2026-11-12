@push('styles')
    <style>
        .guest-shell {
            --school-navy: #12304a;
            --school-blue: #1d5e83;
            --school-gold: #e6b85c;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: clamp(1rem, 4vw, 3rem);
            background:
                radial-gradient(ellipse at 12% 10%, rgba(81, 158, 177, .16), transparent 34rem),
                linear-gradient(145deg, #f3f7fa 0%, #e8f0f5 100%);
        }

        .guest-content {
            width: min(100%, 62rem);
        }

        .guest-footer {
            padding-top: 1.5rem;
            color: #64748b;
            font-size: .8rem;
            text-align: center;
        }

        .login-card {
            overflow: hidden;
            border: 1px solid rgba(18, 48, 74, .08);
            border-radius: 1.25rem;
            background: #fff;
            box-shadow: 0 1.5rem 4rem rgba(18, 48, 74, .14);
        }

        .login-brand-panel {
            position: relative;
            display: flex;
            min-height: 100%;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: clamp(2rem, 5vw, 3.5rem);
            color: #fff;
            background: linear-gradient(150deg, var(--school-navy), var(--school-blue));
        }

        .login-brand-panel::after {
            position: absolute;
            right: -5rem;
            bottom: -7rem;
            width: 18rem;
            height: 18rem;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 50%;
            box-shadow: 0 0 0 2rem rgba(255, 255, 255, .035), 0 0 0 4rem rgba(255, 255, 255, .025);
            content: "";
            pointer-events: none;
        }

        .login-brand-content {
            position: relative;
            z-index: 1;
        }

        .login-school-icon {
            display: inline-flex;
            width: 3.5rem;
            height: 3.5rem;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 1rem;
            color: var(--school-gold);
            background: rgba(255, 255, 255, .1);
            font-size: 1.6rem;
        }

        .login-brand-panel h1 {
            max-width: 20rem;
            font-size: clamp(2rem, 4vw, 2.8rem);
            font-weight: 700;
            letter-spacing: -.04em;
            line-height: 1.12;
        }

        .login-brand-panel p {
            max-width: 24rem;
            color: rgba(255, 255, 255, .78);
            line-height: 1.7;
        }

        .login-brand-note {
            position: relative;
            z-index: 1;
            margin-top: 3rem;
            color: rgba(255, 255, 255, .7);
            font-size: .85rem;
        }

        .login-form-panel {
            padding: clamp(1.5rem, 5vw, 3.5rem);
        }

        .login-title {
            color: #172b3a;
            font-size: 1.65rem;
            font-weight: 700;
            letter-spacing: -.025em;
        }

        .login-subtitle,
        .login-label {
            color: #64748b;
        }

        .login-input-group .input-group-text {
            color: #64748b;
            background: #f8fafc;
        }

        .login-input-group .form-control {
            min-height: 3rem;
            border-left: 0;
        }

        .login-input-group .form-control:focus {
            border-color: #86b7fe;
            box-shadow: none;
        }

        .login-input-group:focus-within .input-group-text,
        .login-input-group:focus-within .form-control {
            border-color: #86b7fe;
        }

        .login-input-group:focus-within {
            border-radius: .375rem;
            box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .15);
        }

        .login-input-group .input-group-text:first-child {
            border-right: 0;
        }

        .login-password-toggle {
            border-left: 0;
            background: #fff;
            color: #526579;
        }

        .login-password-toggle:hover {
            color: var(--school-blue);
            background: #f8fafc;
        }

        .login-submit {
            min-height: 3rem;
            border: 0;
            background: var(--school-blue);
            font-weight: 600;
        }

        .login-submit:hover,
        .login-submit:focus {
            background: var(--school-navy);
        }

        .login-links a {
            color: var(--school-blue);
            font-weight: 600;
            text-decoration: none;
        }

        .login-links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 767.98px) {
            .guest-shell {
                justify-content: flex-start;
                padding: 1rem;
            }

            .login-brand-panel {
                min-height: 0;
                padding: 1.5rem;
            }

            .login-school-icon {
                width: 2.75rem;
                height: 2.75rem;
                margin-bottom: 1rem;
            }

            .login-brand-panel h1 {
                max-width: none;
                font-size: 1.8rem;
            }

            .login-brand-panel p {
                margin-bottom: 0;
            }

            .login-brand-note {
                display: none;
            }
        }
    </style>
@endpush
