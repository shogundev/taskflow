# Taskflow — agent notes

This app runs entirely in Docker; there is no PHP/Composer/Node on the host.
Do not try to install PHP natively or run `laravel/boost` — use the containers:

```bash
docker compose up -d          # start app, nginx, db, redis, adminer
docker compose exec app php artisan <command>
docker compose exec app composer <command>
```

The app is served at http://localhost:8080. See [README.md](README.md) for the
full setup, ports, and demo credentials.

## Why the code looks bad

This project was deliberately built with weak architecture (fat controllers, no
service/repository layer, no policies, duplicated business logic, unrestricted
mass assignment) so it can be used as a refactoring exercise toward clean
architecture / SOLID / dependency inversion. See the "Known architecture smells"
and "Suggested refactor path" sections in [README.md](README.md) before assuming
something is an accidental bug — check whether it's on that list first.
