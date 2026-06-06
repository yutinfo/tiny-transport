# Skill: Generate Feature Test

## Use When

Adding or updating Laravel feature tests for routes, role behavior, forms, database persistence, or driver portal actions.

## Instructions

1. Inspect existing tests under `tests/Feature`.
2. Use `Tests\TestCase` and `RefreshDatabase` when database state matters.
3. Create the smallest set of model records needed for the behavior.
4. Test through HTTP requests when route behavior matters.
5. Assert status codes, redirects, validation errors, rendered text, and database state.
6. Include authorization or ownership failure cases when access control changes.
7. Run the focused test inside Docker.

## Pattern

```php
public function test_user_can_perform_expected_behavior()
{
    $user = User::create([
        'username' => 'example-user',
        'password' => 'password',
        'name' => 'Example',
        'last_name' => 'User',
        'email' => 'example-user@example.com',
        'status' => 'active',
        'role_name' => User::ROLE_ADMIN,
    ]);

    $this->actingAs($user)
        ->get('/admin/dashboard')
        ->assertOk();
}
```

## Command

```bash
docker compose exec app php artisan test --filter=<FocusedFeatureTest>
```
