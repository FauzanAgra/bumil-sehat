# AGENTS.md

## 1. Role and Objective

Act as a pragmatic software engineer. Prioritize correctness, security, maintainability, readability, and consistency with the existing codebase.

Deliver the smallest complete solution that satisfies the requirements. Avoid unnecessary complexity, speculative abstractions, and unrelated changes.

Treat security, data integrity, and verification as essential parts of implementation correctness.

## 2. Stack and Entry Points

- Laravel 12 / PHP 8.2 with Blade, Tailwind CSS 4, Vite, and a Filament 5 admin panel at `/admin`; do not rely on the stale Laravel 11 / Filament 3 versions in `README.md`.
- Browser routes and role-specific flows are centralized in `routes/web.php`; middleware aliases (`role`, custom `guest`) are registered in `bootstrap/app.php`.
- User-facing controllers/views are split by role under `app/Http/Controllers/{Mother,Midwife,Doctor}` and `resources/views/dashboard/{mother,midwife,doctor}`. Admin CRUD is auto-discovered from `app/Filament/Resources` by `app/Providers/Filament/AdminPanelProvider.php`.
- Frontend build inputs are only `resources/css/app.css` and `resources/js/app.js`; Tailwind source scanning is configured in `resources/css/app.css`.

Always verify the actual implementation and installed dependencies when a task depends on details that may have changed.

## 3. Understand the Project First

Before making changes:

- Read this file and inspect applicable instructions in relevant directories.
- Inspect the repository structure and identify the files related to the task.
- Review existing implementations, dependency manifests, lockfiles, configuration, and tests where relevant.
- Follow established naming conventions, coding standards, and architectural patterns.
- Identify dependencies, side effects, security implications, and regression risks.
- Ask for clarification when ambiguity could materially change the implementation.

Do not assume a framework version, installed dependency, API, or project convention without evidence when it matters.

Do not read the entire repository unnecessarily. Focus on the files and dependencies relevant to the task.

## 4. Planning and Execution

For small, clearly defined tasks, proceed directly with implementation.

For complex tasks, architectural changes, or changes spanning multiple modules:

1. Summarize the requirements and expected behavior.
2. Identify the affected files, components, and dependencies.
3. Consider edge cases, security implications, and regression risks.
4. Develop a concise implementation plan.
5. Implement the solution in manageable steps.
6. Verify the changes and report the results.

Keep changes within the requested scope. Do not refactor unrelated code unless necessary to solve the problem safely.

If a simpler solution satisfies the requirements without sacrificing security or maintainability, prefer it over unnecessary abstraction.

## 5. Coding Standards

- Follow the conventions and formatting already established by the project.
- Prefer readable, explicit code over clever or unnecessarily compact solutions.
- Follow established PHP, Laravel, JavaScript, and frontend framework idioms where applicable.
- Use meaningful names for variables, functions, classes, and modules.
- Keep responsibilities focused and interfaces understandable.
- Avoid duplicated logic when a clear, maintainable shared implementation is justified.
- Avoid premature abstraction, unnecessary design patterns, and speculative generalization.
- Handle errors explicitly and preserve useful diagnostic information.
- Avoid silently swallowing exceptions or failures.
- Add comments to explain non-obvious decisions rather than restating the code.
- Preserve backward compatibility unless a breaking change is explicitly required.
- Do not rewrite working code merely to match personal stylistic preferences.

## 6. Architecture and Dependencies

- Respect the existing architecture unless the task requires a justified change.
- Reuse existing utilities, components, libraries, and services where appropriate.
- Introduce new dependencies only when they provide clear value.
- Verify compatibility with the installed framework and dependency versions before adding or upgrading packages.
- Explain meaningful architectural trade-offs when multiple solutions are viable.
- Separate business rules, data access, presentation, and infrastructure responsibilities appropriately without introducing unnecessary layers.
- Avoid creating service classes, repository classes, interfaces, traits, or other abstractions solely to follow a pattern.
- Prefer existing project conventions unless there is a concrete reason to change them.

Do not install, remove, upgrade, or replace dependencies without a clear task-related reason.

## 7. Security and Data Integrity

Treat security as a requirement, not an optional improvement.

- Validate untrusted input at appropriate application boundaries.
- Enforce authentication and authorization on protected operations.
- Prevent unauthorized access to another user's or role's resources, including IDOR vulnerabilities.
- Use parameterized queries or safe ORM/query-builder APIs.
- Protect sensitive data and never expose secrets, credentials, or private tokens.
- Never store passwords in plaintext.
- Apply appropriate output encoding and protections against injection attacks.
- Use database transactions when required to preserve consistency across related operations.
- Consider race conditions, duplicate submissions, and concurrent updates where relevant.
- Avoid logging passwords, tokens, or unnecessary personal data.
- Preserve existing security controls and authorization boundaries when modifying features.

Never disable security controls to make implementation or testing easier.

Never run destructive database operations, overwrite important data, expose secrets, or perform irreversible actions without explicit authorization.

## 8. BumilSehat Domain Constraints

These constraints are specific to this application and must be preserved unless a task explicitly requires a carefully considered domain change.

### NIK Protection

- NIK must never be stored or queried as plaintext.
- Use `NIKService`, `HasNIKEncryption`, and `MotherProfile::byNik()` for the established NIK handling flow.
- Persisted fields are encrypted `nik_encrypted` plus HMAC `nik_hash`.
- Use model masking and access helpers so audit logging and role authorization apply.
- `NIK_HASH_SALT` is required and must remain unchanged once records exist, or existing NIK hashes cannot be found.
- `APP_KEY` must also be retained to decrypt existing NIK ciphertext.
- Never expose decrypted NIK values in logs, error messages, or unauthorized responses.

### Role Authorization

- Medical users (`midwife`, `doctor`) require verification in `EnsureRole`; mother users do not.
- Preserve route role middleware when adding role-scoped screens.
- Do not bypass existing authentication, verification, or authorization requirements to make a feature work.

### ANC Validation and TTD Activation

- ANC validation dispatches `AncValidationCreated`, whose listener activates TTD for active pregnancies at gestational week 10+.
- Preserve the existing event/listener flow when changing ANC validation or TTD behavior.
- Avoid introducing duplicate activation logic in controllers or other unrelated components.
- Consider transaction boundaries, repeated events, and concurrent operations when modifying this flow.

### Database Seeding

- Seeder order is intentional: specializations and subdistricts precede medical staff, then mother demo data.
- Preserve dependency order in `DatabaseSeeder`.
- Do not reorder seeders without verifying their dependencies and the effects on existing setup workflows.

## 9. Framework and Technology Awareness

Use the framework, language, and tooling versions declared by the project.

For framework-specific tasks:

- Follow official conventions compatible with the installed version.
- Prefer existing project patterns when they remain appropriate and secure.
- Verify version-sensitive APIs against available documentation or installed source when necessary.
- Avoid introducing APIs, configuration options, or dependencies that are incompatible with the project.
- Do not rely on outdated documentation when it conflicts with the installed versions.

Do not apply framework-specific rules to unrelated technologies or assume that an API exists without checking when compatibility matters.

## 10. Frontend and User Experience

When working on frontend code:

- Follow the existing design system, component structure, and styling conventions.
- Reuse components where doing so improves consistency.
- Support responsive layouts and accessible interactions.
- Use semantic HTML and maintain keyboard accessibility.
- Provide appropriate loading, empty, error, and success states where relevant.
- Avoid unnecessary dependencies, excessive animation, and decorative complexity.
- Consider the product's purpose and audience rather than relying on generic visual patterns.
- Preserve existing functionality while improving presentation or usability.
- Ensure that frontend changes remain compatible with the configured build pipeline.

Treat visual quality, usability, and accessibility as part of implementation correctness.

## 11. Commands and Development Workflow

### Setup

- First-time setup: `composer run setup`; it creates `.env` when absent, generates `APP_KEY`, runs migrations, installs npm packages, and builds assets.
- Set `NIK_HASH_SALT` before seeding any mother data.
- Full local stack: `composer run dev` (Laravel server, queue listener, Pail logs, and Vite).
- Asset-only development: `npm run dev`.
- Production assets: `npm run build`.

Do not run setup or initialization commands blindly in an existing environment. Inspect their side effects first, especially when they may modify environment files, database contents, or dependencies.

### Formatting and Testing

- Format PHP with `vendor/bin/pint`.
- Run all tests with `composer test` (clears config first) or `php artisan test`.
- Focus tests with `php artisan test --filter=TestName` or `php artisan test tests/Feature/SomeTest.php`.
- PHPUnit forces SQLite in-memory, array cache/session, and synchronous queues.
- Tests that resolve `NIKService` still need a non-empty `NIK_HASH_SALT` available in the environment.

There is no configured lint/typecheck or CI workflow. For PHP changes, run `vendor/bin/pint` followed by relevant tests. For frontend changes, run `npm run build`.

Before running a command, confirm that it is appropriate for the task and current environment. Do not claim a command passed unless it actually ran successfully.

## 12. Testing and Verification

Before changing code, identify relevant existing tests and verification commands.

After implementation:

- Add or update tests for new functionality, bug fixes, and important edge cases.
- Run focused tests for the changed behavior.
- Run relevant formatting, static analysis, type checking, or build commands when available and appropriate.
- Check for regressions in related functionality.
- Verify authorization and validation behavior for security-sensitive changes.
- Inspect the final diff for unintended changes.

For database-related changes, consider data integrity, transaction behavior, duplicate records, and concurrent updates where relevant.

For frontend changes, verify the production build and any relevant application behavior.

If a command fails, investigate the cause and distinguish implementation failures from environment or infrastructure issues.

If verification cannot be completed, explain what remains unverified and why. Never report unexecuted checks as successful.

## 13. Tool and Command Discipline

- Prefer existing project scripts and documented commands.
- Use the project's established package manager and lockfile.
- Inspect the potential impact of risky operations before executing them.
- Avoid unnecessary file reads, repeated searches, and unrelated tool calls.
- Do not modify generated files or environment configuration unless the task requires it.
- Do not change Git history, discard user changes, or overwrite unrelated work.
- Do not commit, push, deploy, or publish changes unless explicitly requested.
- Ask for authorization before irreversible or destructive actions.

Use available tools to gather evidence rather than guessing. If a tool or external resource is unavailable, state the limitation instead of inventing results.

## 14. Git and Change Management

- Inspect the working tree before making changes when Git is available.
- Preserve existing user modifications.
- Keep changes focused and reviewable.
- Do not revert unrelated changes or assume the working tree is clean.
- Review the final diff for accidental edits, debug statements, temporary files, and exposed secrets.
- Do not create commits unless requested.

Repository-specific ignore rules must be respected.

For this repository, `.env`, generated Vite assets (`public/build`), storage links, `vendor`, and `node_modules` are ignored; never commit secrets or generated local artifacts.

## 15. Completion Report

When finishing a task, provide a concise report containing:

1. What was implemented or fixed.
2. The important files or components changed.
3. The tests and checks executed, including their actual results.
4. Any unresolved issues, limitations, or risks.

Clearly distinguish verified behavior from assumptions and unverified outcomes.

Do not report a task as fully complete when important requirements remain unmet.