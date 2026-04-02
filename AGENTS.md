# AGENTS.md

Guidance for coding agents working in this repository.

## 1. Project Snapshot
- App: ResQnet PHP platform.
- Runtime: PHP 8+.
- Style: custom function-based MVC + router.
- Entry point: public/index.php.
- Boot sequence: core/bootstrap.php -> routes.php -> dispatch().
- Modules: each module is under modules/<name>/ with optional models.php, controllers.php, and views/.

## 2. Run and Validate
- Install dependencies: composer install
- Start local server: composer serve
- Equivalent start command: composer start
- Default dev URL: http://localhost:8001
- Quick syntax check (single file): php -l <path>
- Recommended multi-file check after edits:
  - php -l routes.php
  - php -l core/helpers.php
  - php -l modules/auth/controllers.php

## 3. Routing and Request Lifecycle
1. Request enters public/index.php.
2. bootstrap loads env/session/core files and auto-requires module models/controllers.
3. routes.php registers route table via route(method, path, handler, middlewares).
4. dispatch() matches method + URI.
5. Matched middleware chain runs first.
6. Handler function is invoked.
7. Handler returns view(...) or redirect(...).

Key files:
- public/index.php
- core/bootstrap.php
- core/router.php
- core/middleware.php
- routes.php

## 4. Conventions to Follow
- Keep functions small and explicit.
- For POST handlers, call csrf_check() first.
- Use flash() + redirect() for validation and PRG flow.
- Use request_input() for request values.
- Use old() / flash_old_input() for form refill.
- Escape output with e(...).
- Do not add framework dependencies unless requested.

## 5. Views and Layouts
- Global layouts are in views/layouts/.
- Module views use module syntax: view('auth::login', ...).
- If a page must match a standalone template exactly, render without layout wrapper (view('module::page')).
- Template references are in template/.
- If template CSS/image assets are needed at runtime, place web-served copies under public/assets/.

## 6. Auth and Role Notes
- Roles in current system include general, volunteer, ngo, grama_niladhari, dmc.
- Some code paths include legacy compatibility aliases. Preserve compatibility unless explicitly removing it.
- Auth middleware:
  - middleware_guest(): blocks authenticated users from guest-only routes.
  - middleware_auth(): requires login.
  - middleware_role()/middleware_roles(): role guards.

## 7. Database and Schema Safety
- This codebase contains compatibility logic for schema differences (legacy vs newer structure).
- Before hard-coding DB column names, inspect modules/auth/models.php and existing helper methods.
- Prefer extending existing model helper functions over direct SQL in controllers.

## 8. Editing Rules
- Do not edit vendor/.
- Do not commit secrets or .env values.
- Keep changes focused; avoid unrelated formatting churn.
- If you touch routes/controllers/views together, validate all touched files with php -l.

## 9. Done Checklist
- Route wiring added or updated where needed.
- Middleware is correct for auth/role boundaries.
- View path and layout choice are intentional.
- Flash messages and redirects behave correctly.
- All edited PHP files pass php -l.
