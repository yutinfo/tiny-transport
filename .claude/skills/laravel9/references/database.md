# Database — migrations, columns, indexes, seeders

## Every schema change is a migration
- One migration per change, with a working `up()` **and** `down()`. The `down()`
  must actually reverse `up()` (drop the column/table/index it added).
- Run and validate through Docker:
  ```bash
  docker compose exec app php artisan migrate
  docker compose exec app php artisan migrate:rollback   # prove down() works
  docker compose exec app php artisan migrate:status
  ```

## Adding / changing columns
- Check current model + query usage before adding or renaming a column.
- Use **nullable** (or a default) when existing rows won't have the new value —
  backward compatibility for records created before the migration.
- Renaming a column requires `doctrine/dbal`; confirm it's installed before
  writing a `->change()`/rename migration, and grep every `->column` / Blade /
  request-field usage.

## Indexes
- Add an index when a new column is filtered/sorted/joined on a hot path
  (dashboard, trip/parcel lists). Note the index impact in your summary.
- Existing migrations already add indexes to `order_receives` — follow that
  pattern rather than inventing a new naming scheme.

## Seeders
- Seed data lives in `database/seeders/`. The default admin account
  (`admin` / `password`) comes from the seeder.
- Create only the records a change needs; don't bloat seeders with test fixtures
  (that belongs in feature-test factories/setup).

## Impact note (include in every DB change summary)
1. **Data impact** — which existing rows are affected, backfill needed or not.
2. **Index impact** — new/changed indexes and the queries they serve.
3. **Rollback** — what `down()` does and whether it's data-safe.
