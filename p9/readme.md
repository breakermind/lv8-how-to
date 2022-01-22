# Testing

### Testowanie zalogowanego użytkownika phpunit
```php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\WebiSeeder;
use App\Models\User;

abstract class AuthenticatedTestCase extends TestCase
{
	use RefreshDatabase; // Refresh db before each test

	protected $user; // Logged user

	protected $authWithRole = 'user'; // Logged user role

	protected $seed = true; // Run seeder before each test.

	protected $seeder = AuthSeeder::class; // Choose seeder class

	protected function setUp(): void
	{
		parent::setUp();

		$this->user = User::factory()->role($this->authWithRole)->create(); // Create user in db

		$this->actingAs($this->user); // Logged user
	}

	function seedAuth()
	{
		$this->seed(AuthSeeder::class);
	}

	function getPassword($html)
	{
		preg_match('/word>[a-zA-Z0-9]+<\/pass/', $html, $matches, PREG_OFFSET_CAPTURE);
		return str_replace(['word>', '</pass'], '', end($matches)[0]);
	}
}
```
