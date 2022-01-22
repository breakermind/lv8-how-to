# Testing
Testowanie phpunit w Laravel.

### Migracje bazy danych
```sh
php artisan --env=testing migrate:fresh --seed
```

### Uruchom test
```sh
php artisan test --stop-on-failure
```

## Baza danych i użytkownik
mysql -u root -p
```sql
CREATE DATABASE IF NOT EXISTS app_xx_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON *.* TO app_xx@127.0.0.1 IDENTIFIED BY 'toor' WITH GRANT OPTION;
FLUSH PRIVILEGES;
```

### Ustawienia
.env.testing
```sh
APP_ENV=testing
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=app_xx_testing
DB_USERNAME=app_xx
DB_PASSWORD=toor

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120
# SESSION_SECURE_COOKIE=true
```

### Logowanie użytkownika phpunit
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
