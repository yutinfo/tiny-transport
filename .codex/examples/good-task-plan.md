# Example Task Plan

## Goal

Add a small, reviewable behavior change with focused validation.

## Files

- Modify: `app/Http/Controllers/ExampleController.php`
- Modify: `resources/views/admin/example/show.blade.php`
- Test: `tests/Feature/ExampleFeatureTest.php`
- Optional modify: `README.md` if route behavior changes

## Steps

- [ ] Inspect current route, controller, view, and related tests.
- [ ] Add or update a focused feature test for the changed behavior.
- [ ] Run the focused test and confirm it fails for the expected reason.
- [ ] Implement the smallest controller or view change.
- [ ] Run the focused test again.
- [ ] Run broader checks if shared behavior changed.
- [ ] Review the diff.
- [ ] Update README if user-facing route or workflow behavior changed.

## Validation

```bash
docker compose exec app php artisan test --filter=ExampleFeatureTest
git diff --check
```

## Acceptance Criteria

- The requested behavior works.
- Existing related behavior is preserved.
- Focused test passes.
- No generated public asset files are edited by hand.
