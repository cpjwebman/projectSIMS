# Production Deployment Checklist

This project is prepared for production configuration, but hosting-specific values must be supplied before going live. Never commit `.env` or paste credentials into source files, documentation, or support requests.

## Hosting requirements

- Use PHP 8.2 or newer and install the extensions required by Composer and the application. Enable `pdo_mysql` when using MySQL. Check the target host with `composer check-platform-reqs --no-dev`.
- Point the web server document root at the project's `public` directory. Do not expose the project root, `.env`, `vendor`, `storage`, or `bootstrap`.
- Enable HTTPS and configure the host's HTTP-to-HTTPS redirect. Add HSTS only after HTTPS is confirmed for the whole hostname and all subdomains.
- Grant the PHP/web-server account write access to `storage` and `bootstrap/cache`. Do not make the entire project world-writable.
- Confirm Apache permits the project's `.htaccess` directives (`mod_rewrite`, `mod_headers`, and authorization directives). Do not deploy if Apache returns a 500 error after applying `.htaccess`.

## Host `.env`

The checked-in local environment file has been set to `APP_ENV=production`, `APP_DEBUG=false`, and `LOG_LEVEL=warning`. Before deploying, configure the actual host's ignored `.env` file with:

- `APP_URL`: the real HTTPS origin, required for correct password-reset and other generated links.
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`: provider-issued production database settings. Use a dedicated least-privilege database account.
- `MAIL_MAILER`, `MAIL_SCHEME`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME`: provider-issued outbound mail settings. `MAIL_MAILER=log` does not deliver email.
- `SESSION_ENCRYPT=true` and `SESSION_SECURE_COOKIE=true` when HTTPS is active. Keep `SESSION_HTTP_ONLY=true` and `SESSION_SAME_SITE=lax`; use a shared session/cache store if the deployment has multiple application instances.
- Preserve the existing `APP_KEY` when deploying an existing installation. Generate it only for a new application; changing it invalidates encrypted data and active sessions.
- `FILESYSTEM_THROW=true` so storage failures raise errors and are logged instead of being silently treated as success.

Do not run configuration or route caching until the host `.env` contains the real production values. The local `APP_URL`, database, and mail configuration are not production-ready.

## Build and deploy

1. Back up the production database and uploaded files. Review the migration list and application changes before applying any migration to production.
2. Deploy source and lockfiles, then install production PHP dependencies and build frontend assets:

   ```sh
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   ```

3. Configure the host `.env`; verify the document root and writable directories; then run:

   ```sh
   php artisan migrate --force
   php artisan storage:link
   php artisan optimize
   ```

   Run `storage:link` only when the host link is missing; verify that `public/storage` points to `storage/app/public`.
4. Verify the HTTPS login and password-reset flows, email delivery, authenticated pages, profile-photo upload/removal, and application logs. Confirm debug output is disabled and no `.env` or project files are web-accessible.
5. On each release, use the same lockfiles and rebuild caches on the host after its environment is configured. Clear/rebuild Laravel caches as part of the release process when environment or route configuration changes.

## Findings and operational notes

- Composer initially reported seven Symfony advisories across four packages. The lockfile has been updated to patched Symfony `7.4.23` releases; rerun `composer audit --no-dev` during release checks.
- `npm audit` reported no known vulnerabilities at audit time. Re-run it when dependencies change.
- All migrations are applied to the inspected local database only; that does not indicate the state of the production database. Back up production before migrating.
- Student profile photos are stored on the public disk and rendered with public URLs. Anyone who obtains a photo URL can access that image. If photos must be private, a separate authenticated-download design is required.
- `public/storage` is currently a Windows junction in the development environment. The production host must create and verify its own symlink (or hosting-supported equivalent).
- Security headers and sensitive-file denials are set in `public/.htaccess`. The policy intentionally does not set a Content Security Policy because the existing frontend uses external assets; define and test CSP separately if the asset policy changes.
- On the inspected Windows/Laragon workspace, file-system ACL behavior does not represent the Linux/shared-hosting permissions. Verify ownership and write access on the actual host.
