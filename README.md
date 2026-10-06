# Taskflow

A small Laravel project/task tracker (Users → Projects → Tasks → Comments), built
**on purpose** with a bad architecture. The goal is to have a working app to later
refactor into something following clean architecture / SOLID / dependency inversion,
and to use as a sandbox for learning Docker-based CI/CD and deployment.

## Stack

- PHP 8.4-FPM, Laravel 13
- MySQL 8, Redis 7
- nginx (serves the app, proxies PHP to the `app` container)
- Adminer (DB browser)

No PHP/Composer/Node install is required on the host — everything runs in Docker.

## Running it

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

| Service  | URL                          |
|----------|------------------------------|
| App      | http://localhost:8080        |
| Adminer  | http://localhost:8082        |
| MySQL    | localhost:3308               |
| Redis    | localhost:6381               |

Demo login (created by the seeder): `test@example.com` / `password`.

Common commands:

```bash
docker compose exec app php artisan <command>   # artisan
docker compose exec app composer <command>       # composer
docker compose logs -f app                       # tail app logs
docker compose down                              # stop everything
docker compose down -v                           # stop and wipe the DB volume
```

## Tests

PHPUnit, using in-memory SQLite (configured in `phpunit.xml`), so no MySQL/Redis is
needed to run them — this is what a CI pipeline should call.

```bash
docker compose exec app php artisan test            # inside the running stack
docker compose run --rm app php artisan test        # one-off container (what CI will do)
```

The suite covers behavior only (HTTP responses, DB state, authorization), not
implementation, so it should keep passing unchanged through the architecture refactor.

## Domain

- A `User` owns `Project`s.
- A `Project` has many `Task`s (`status`: todo/in_progress/done, `priority`, `due_date`).
- A `Task` has many `Comment`s from any user.

## Known architecture smells (intentional)

This is the punch list for the later "refactor to clean architecture" pass. Nothing
here is accidental — it's meant to be recognizable and fixable one principle at a time.

- **Fat controllers.** All validation, authorization, and persistence logic lives
  directly in `app/Http/Controllers/*`. There is no service layer, no use-case/action
  classes, no repositories — controllers talk straight to Eloquent.
- **No dependency inversion.** Controllers `use App\Models\X` directly. There are no
  interfaces/contracts, so nothing can be swapped or mocked without touching Eloquent.
- **Duplicated authorization.** The `$project->user_id !== auth()->id()` (and the
  task/comment equivalents) check is copy-pasted in nearly every controller method
  instead of living in a single `ProjectPolicy`/`TaskPolicy`.
- **No Form Requests.** Validation rules are inlined with `$request->validate()` in
  every controller method, duplicated between `store` and `update`.
- **Unrestricted mass assignment.** `Project`, `Task`, and `Comment` use
  `protected $guarded = []`, and controllers call `Model::create($request->all())` —
  a client can set any column it wants.
- **Duplicated business rules.** "Is this task overdue?" is computed independently
  in `DashboardController`, `TaskController`, and inline in `projects/show.blade.php`
  — three copies of the same rule, easy to get out of sync.
- **N+1 queries.** `DashboardController::index()` loops over projects and lazy-loads
  `->tasks` inside the loop instead of eager loading.
- **Logic in views.** `projects/show.blade.php` computes overdue status and status
  styling with inline `@php` blocks instead of a view model/presenter.
- **No events/jobs.** Everything (e.g. comment notifications, if added) would have to
  be bolted directly into controllers; there's no domain event bus.
- **No API layer.** Only server-rendered Blade views exist; there's no versioned API,
  no API Resources/transformers.

## Suggested refactor path

1. Extract `ProjectPolicy` / `TaskPolicy` / `CommentPolicy` to kill the duplicated
   `auth()->id()` checks (Laravel's built-in authorization, not custom abstractions).
2. Add Form Requests (`StoreProjectRequest`, etc.) to move validation out of controllers.
3. Add `$fillable` (or DTOs) to stop uncontrolled mass assignment.
4. Introduce a service/action layer (e.g. `CreateTask`, `ToggleTaskStatus`) so
   controllers only orchestrate HTTP concerns.
5. Push overdue/status logic into one place (a scope + accessor, or a small domain
   object) and delete the three duplicated copies.
6. Introduce repository interfaces + Eloquent implementations bound in a service
   provider, to demonstrate dependency inversion concretely.
7. Keep the existing test suite green after every step; add unit tests for new
   services/repositories as they appear.
