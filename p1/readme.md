# Exceptions Handler, Middleware
Przechwytywanie i logowanie błędów w aplikacji.

### Exceptions Handler

#### Json Errors
app/Exceptions/Handler.php
```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{	
	// A list of the exception types that are not reported.	 
	protected $dontReport = [
		// InvalidOrderException::class,
	];

	// A list of the inputs that are never flashed for validation exceptions.
	protected $dontFlash = [
		'current_password',
		'password',
		'password_confirmation',
	];

	// Register the exception handling callbacks for the application.
	public function register()
	{
		$this->reportable(function (Throwable $e) {
			// Stop logging to lavavel logs
			// return false;
		});
		
		$this->renderable(function (Throwable $e, $request) {
			// For urls
			if (
				$request->is('web/*') ||
				$request->is('api/*') ||
				$request->wantsJson()
			) {
				// Get exception
				$msg = empty($e->getMessage()) ? 'Not Found' : $e->getMessage();
				$code = empty($e->getCode()) ? 404 : $e->getCode();
				
				// Json response
				return response()->json([
					'message' => $msg,
					'code' => $code,
					'ex' => [
						'name' => $this->getClassName(get_class($e)),
						'namespace' => get_class($e),
					]
				], $code);
			}
		});		
	}

	// Get exception class name without namespace.
	static function getClassName($e) {
		$path = explode('\\', $e);
		return array_pop($path);
	}
}
```
### Middleware

#### Utwórz migrację modelu
```sh
php artisan make:migration update_users_table
```

#### Dodaj kolumny do modelu User
database/migrations/9000_01_01_100002_update_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function (Blueprint $table) 
		{	
			// $table->engine = 'InnoDB';
			// $table->charset = 'utf8mb4';
    			// $table->collation = 'utf8mb4_unicode_ci';
    
			if (!Schema::hasColumn('users', 'role')) {
				$table->enum('role', ['user','worker','admin'])->nullable()->default('user');
			}
			if (!Schema::hasColumn('users', 'remember_token')) {
				$table->string('remember_token')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'email_verified_at')) {
				$table->timestamp('email_verified_at')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'code')) {
				$table->string('code', 128)->unique()->nullable(true);
			}
			if (!Schema::hasColumn('users', 'ip')) {
				$table->string('ip')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'deleted_at')) {
				$table->softDeletes();
			}
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(['code', 'ip', 'role', 'remember_token', 'deleted_at']);
		});
	}
}
```

#### Middleware uprawnienia użytkownika z parametrami
app/Http/Middleware/AuthenticateRoles.php
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthenticateRoles
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	public function handle(Request $request, Closure $next, $role = '')
	{
		$roles = array_filter(explode('|', trim($role)));

		if (! empty($roles)) {
			if (Auth::check()) {
				$user = Auth::user();

				if (! in_array($user->role, $roles)) {
					throw new Exception("Unauthorized Role", 401);
				}
			} else {
				throw new Exception("Unauthorized User", 401);
			}
		}

		return $next($request);
	}
}
```

#### Rejestracja middleware
app/Http/Kernel.php
```php
<?php
protected $routeMiddleware = [
	'role' => \App\Http\Middleware\AuthenticateRoles::class,
];
```

#### Wykorzystanie w procesie autoryzacji
```php
<?php
// Pogrupowane
Route::prefix('web/api')->name('web.api.')->middleware(['web'])->group(function() {
	
	// Linki publiczne	
	// Route::get('/version', [UserController::class, 'version'])->name('version');
	
	// Linki prywatne, zalogowani użytkownicy
	Route::middleware(['auth', 'role:admin|worker|user'])->group(function () {
		// Route::get('/test/user', [UserController::class, 'test'])->name('test.user');
		// Route::get('/logout', [UserController::class, 'logout'])->name('logout');		
	});

	// Linki prywatne, tylko admin i worker
	Route::middleware(['auth', 'role:admin|worker'])->group(function () {
		// Route::get('/test/worker', [WorkerController::class, 'test'])->name('test.worker');
	});
	
	// Linki prywatne, tylko admin
	Route::middleware(['auth', 'role:admin'])->group(function () {
		// Route::get('/test/admin', [AdminController::class, 'test'])->name('test.admin');
	});
}

// Lub pojedyńczo
Route::get('/profile', function () {	
	response()->json([
		'message' => 'User profil',
		'user' => [
			'id' => 1,
			'name' => 'Ala'
		]
	]);
})->middleware(['auth', 'role:admin|worker|user']); // Zalogowany użytkownik
```
