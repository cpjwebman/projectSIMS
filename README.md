# Student Management System

A Laravel 12 application for managing student records, academic programs, subject catalogs, school-year enrollments, and enrollment approvals. The application provides role-based web portals and a Sanctum-protected JSON API for student records.

## Features

- Admin/staff management for students, courses, year levels, subjects, and enrollments.
- Student portal for profile maintenance and submitting/cancelling pending enrollment requests.
- Enrollment review and approval, with course/year-level-specific subject assignments.
- User access management for administrators.
- Dashboard statistics, date/status filters, enrollment charts, course counts, and recent activity.
- Sanctum bearer-token API for student CRUD and an authenticated-user endpoint.

## Requirements

- PHP 8.2 or newer and Composer.
- Node.js and npm for compiling frontend assets.
- A Laravel-supported database. The example environment is configured for SQLite.

## Local setup

From the project directory:

```powershell
composer install
if (!(Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
npm install
```

Configure `.env` for the local environment and database. Do not commit `.env` or production credentials. For the default SQLite configuration, create the database file only if it does not already exist:

```powershell
if (!(Test-Path database\database.sqlite)) { New-Item -ItemType File -Path database\database.sqlite }
php artisan migrate --seed
npm run build
php artisan storage:link
```

Start the application:

```powershell
php artisan serve
```

Open the URL printed by Artisan. For frontend development, run `npm run dev` in a separate terminal.

The default database seeder creates a test user (`test@example.com`). For a local-only administrator, promote an existing account:

```powershell
php artisan users:make-admin test@example.com
```

This command grants administrator access and activates the selected account. Use it only with an account you control.

## Roles and access

- **Admin:** User management and all academic-management functions.
- **Staff:** Academic-management functions and the staff dashboard.
- **Student:** Own profile and enrollment self-service.

Role-protected routes also require an active account. The staff dashboard additionally requires verified email. Newly registered accounts are created as inactive student accounts pending administrator approval.

## Main areas

- `/dashboard` — staff/admin statistics and enrollment activity.
- `/student/dashboard` — student's linked profile and academic enrollment history.
- `/students`, `/courses`, `/subjects`, `/year-levels`, `/enrollments` — staff/admin academic management.
- `/users` — admin-only account access management.
- `/api/students` — Sanctum-authenticated student JSON API.

Use `php artisan route:list` to inspect the complete route list. See:

- [Database schema and migration guide](docs/DATABASE_SCHEMA.html)
- [Controllers, CRUD and web routes](docs/LARAVEL_CONTROLLERS_CRUD_ROUTES.html)
- [API documentation](docs/API_DOCUMENTATION.html)
- [Dashboard reports documentation](docs/REPORTS_DOCUMENTATION.html)
- [Bug, validation and security review](docs/BUG_VALIDATION_SECURITY_REVIEW.html)
- [Automated testing and Laravel tests](docs/AUTOMATED_TESTING.html)
- [Production deployment checklist](docs/PRODUCTION_DEPLOYMENT.md)

PDF versions of the README, API reference, dashboard/report guide, and other project references are available in `docs/`.

## API quick start

All student API endpoints require a Sanctum personal access token belonging to an active admin or staff account:

```http
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

The API base path is `/api`. See [API_DOCUMENTATION.html](docs/API_DOCUMENTATION.html) for endpoint details, validation fields, response examples, and error handling.

## Tests

The PHPUnit configuration uses an in-memory SQLite database by default, isolating tests from the database configured in `.env`.

Run the complete suite:

```bash
php artisan test
```

Run focused tests with Pest's name filter, for example:

```bash
php artisan test --filter=StudentApiTest
php artisan test --filter=EnrollmentTest
php artisan test --testsuite=Unit
```

Feature tests use `RefreshDatabase`; the in-memory database is rebuilt for the test run. If database environment variables are explicitly overridden, make sure they point to a disposable test database. See [Automated Testing](docs/AUTOMATED_TESTING.html) for test coverage, examples, and workflow guidance.

## Frontend assets

```powershell
npm run dev
npm run build
```

## Application documentation

The HTML files in `docs/` are the editable sources used to generate the matching PDF references. Update the source HTML (and this README, where applicable) when application behavior changes, then regenerate the PDFs.

## Production deployment

Review [the production deployment checklist](docs/PRODUCTION_DEPLOYMENT.md) before hosting. The host must provide the real HTTPS URL, database settings, outbound mail settings, and writable Laravel runtime directories. Never deploy with the local `.env` values or expose the project root as the web document root.
