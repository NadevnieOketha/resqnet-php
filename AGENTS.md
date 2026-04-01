# AGENTS.md

Guidance for coding agents working in this repository.

## Project Snapshot

- Stack: plain PHP 8+, Composer autoload, MySQL, server-rendered views.
- Entry point: `public/index.php`.
- Boot sequence: `core/bootstrap.php` loads env, core files, then auto-loads each module's `models.php` and `controllers.php`.
- Routing: `routes.php` with `route(method, path, handler, middlewares)` from `core/router.php`.

## Local Run Commands

1. Install dependencies:

   ```bash
   composer install
   ```

2. Configure env:

   ```bash
   cp .env.example .env
   ```

3. Serve app:

   ```bash
   composer serve
   ```

- Default URL in config is `http://localhost:8000`, but the Composer script serves on port `8001`.

## Architecture Conventions

- Keep feature code inside `modules/{feature}/`:
  - `controllers.php`: request handlers
  - `models.php`: DB access functions
  - `views/*.php`: module templates
- Use global helpers in `core/helpers.php` for common concerns:
  - rendering (`view`)
  - redirects/abort
  - auth/session helpers
  - CSRF helpers (`csrf_field`, `csrf_check`)
- Views should escape user-controlled output via `e()` or equivalent safe escaping.

## Routing and Middleware Rules

- Add new routes only in `routes.php`.
- Follow existing handler naming style: `{module}_{action}`.
- Protect state-changing routes with middleware and CSRF validation.
- Role checks are done with middleware closures, for example:

  ```php
  ['middleware_auth', fn() => middleware_roles(['ngo', 'dmc_admin'])]
  ```

## Database and Schema Notes

- SQL schema file: `database/schema.sql`.
- Current code in `modules/auth` and several helpers still assumes a single `users` table shape (`id`, `name`, `email`, `password`, `role`).
- `implementation_plan.md` documents a target migration to role-profile tables and `password_hash`. Treat that as planned work unless the task explicitly asks for migration implementation.

## Change Safety Checklist

Before finalizing changes:

1. Keep edits minimal and scoped to the requested task.
2. Do not rename public handlers/functions unless all references are updated.
3. Preserve backward-compatible route paths unless asked otherwise.
4. Run quick syntax checks for touched PHP files:

   ```bash
   php -l path/to/file.php
   ```

5. If behavior changes, verify the related route flow manually.

## Agent Working Style for This Repo

- Prefer small, targeted patches over broad refactors.
- Reuse existing helper functions and module patterns before adding new abstractions.
- When adding UI, match existing layout usage (`views/layouts/main.php` or dashboard layout).
- For new form POST handlers, always include:
  - `csrf_check()`
  - validation + flash messages
  - redirect-after-post

## Out of Scope Unless Requested

- Large schema rewrites across all modules.
- Replacing the custom router with a framework.
- Unrelated style-only reformatting.