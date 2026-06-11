# Testing — Laravel feature tests

## Where and how
- Feature tests live in `tests/Feature/`. Follow the existing test style before
  introducing a new one.
- Run inside Docker:
  ```bash
  docker compose exec app php artisan test                       # all
  docker compose exec app php artisan test --filter=TripTest      # focused
  docker compose exec app ./vendor/bin/phpunit --filter=foo
  ```
- Run the **focused** test first, then broaden to the full suite when shared
  behavior, routes, middleware, schema, or assets changed.

## Writing a feature test
- Use `RefreshDatabase` when the test depends on DB state.
- Create only the records the test needs — authenticate the right user
  (admin/staff vs driver), hit the route, assert the outcome.
- Assert through observable behavior:
  - HTTP status / redirect (`assertOk`, `assertRedirect`, `assertForbidden`)
  - rendered text (`assertSee`)
  - database state (`assertDatabaseHas` / `assertDatabaseMissing`)
- For access control, assert **both** directions: the allowed user gets in, the
  forbidden user is blocked (driver can't reach admin routes; a driver only sees
  trips where `trips.driver_user_id` matches them).

## Don't
- Don't delete or weaken a test to make a check pass.
- Don't assert on implementation details (internal method calls) when an HTTP +
  DB assertion proves the behavior.
- Don't claim a test passed unless it actually ran — quote the result.
