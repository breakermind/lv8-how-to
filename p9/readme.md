# Testing, Upload Image, Resize Image, Email Message, Api Resources
Testowanie phpunit w Laravel.

### Migracje bazy danych
```sh
php artisan --env=testing migrate:fresh --seed
```

### Uruchom test
```sh
php artisan test --stop-on-failure
```

### Dodaj testsuit
phpunit.xml
```
<testsuites>
	<testsuite name="Restaurant">
		<directory suffix="Test.php">./tests/Feature/Panel/Restaurant</directory>
	</testsuite>
</testsuites>
```

### Uruchom
```sh
php artisan test --testsuite=Restaurant --stop-on-failure
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
use Database\Seeders\AuthSeeder;
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

### Testowanie wysyłania zdjęcia
```php
<?php

namespace Tests\Feature\Auth;

use Tests\Feature\Auth\AuthenticatedTestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UpdateUserTest extends AuthenticatedTestCase
{
	use RefreshDatabase;

	/** @test */
	function upload_only_avatar()
	{	
		Storage::fake('public');

		$this->assertNotEmpty($this->user->id);

		$res = $this->json(
			'POST', '/web/api/update', [
				'name' => 'Johny Bravo',
				'avatar' => UploadedFile::fake()->image('photo.jpg', 512, 512)->size(1024), // Kb
				// 'pdf' => UploadedFile::fake()->create('document.pdf', 1024), // Kb
			]
		);

		$res->assertStatus(200)->assertJson([
			'message' => 'A profil has been updated.'
		]);

		$path = 'avatars/' . $this->user->id . '.jpg';

		Storage::disk('public')->assertExists($path);
		
		/*
		    Or if in .env file:
		    FILESYSTEM_DISK=public
		*/		
		// Storage::assertExists($path);
		
		$res = $this->get(Storage::url($path));
		$res->assertOk();

		$this->assertDatabaseHas('users', [
			'id' => $this->user->id,
			'avatar' => $path
		]);
	}
}
```

### Upload kontroler
Zmiana rozmiaru plików: https://image.intervention.io/v2/introduction/installation
```php
<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateRequest;
use Illuminate\Support\Facades\Storage;
// Resize
// use Intervention\Image\ImageManagerStatic as Image;

class UploadController extends Controller {

	protected $disk = 'public';
	protected $width = 256;
	protected $height = 256;

	function update(UpdateRequest $r)
	{
		$valid = $r->validated();

		if(Auth::check()) {
			try {
				$user = Auth::user();

				$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'];

				if($r->hasFile('avatar')) {
					foreach ($allowed as $ext) {
						$f = 'avatars/' . $user->id . '.' . $ext;
						if (Storage::disk($this->disk)->exists($f)) {
							Storage::disk($this->disk)->delete($f);
						}
					}

					$path = Storage::disk($this->disk)->putFileAs(
						'avatars', $r->file('avatar'), $user->id . '.' . $r->file('avatar')->extension()
					);

					if (Storage::disk($this->disk)->exists($path)) {
						$valid['avatar'] = $path;
						
						// Resize
						// Image::make(Storage::path($path))->resize($this->width, $this->height)->save();
					} else {
						unset($valid['avatar']);
					}
				}

				User::where(['id' => $user->id])->update($valid);
			} catch (Exception $e) {
				report($e);
				throw new Exception(trans("Update error."), 422);
			}
		}	

		return response()->json(['message' => trans('A profil has been updated.')]);
	}
}
```

### Walidacja zdjęcia
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
	protected $stopOnFirstFailure = true;

	public function authorize()
	{
		if(!empty(session('locale'))){
			app()->setLocale(session('locale'));
		}

		return true; // Allow all
	}

	public function rules()
	{
		$email = 'email:rfc,dns';
		if(config('app.debug') == true) {
			$email = 'email';
		}

		return [
			'name' => 'required|max:50',
			'locale' => 'sometimes|size:2',
			'newsletter_on' => 'sometimes|boolean',
			'bio' => 'sometimes|max:250',
			'mobile' => 'sometimes|max:50',
			'avatar' => 'sometimes|image|mimes:jpg,png,jpeg,webp|max:2048|dimensions:min_width=1,min_height=1,max_width=1920,max_height=1080',
		];
	}

	public function failedValidation(Validator $validator)
	{
		throw new \Exception($validator->errors()->first(), 422);
	}

	function prepareForValidation()
	{
		$this->merge(
			collect(request()->json()->all())->only([
				'name', 'bio', 'locale', 'mobile', 'avatar', 'newsletter_on'
			])->toArray()
		);
	}
}
```

### Upload kilku zdjęć
```php
// Input field files[] array 
<form enctype="multipart/form-data" method="POST" action=""> 
    <input type='file' name='files[]' multiple />
    <button type='submit'>Upload</button>
</form>

<?php

$all = collect(request()->file('files'));
$all->each(fn ($img) {
	if($img->extension() == 'webp') {
		$img->move(public_path('images'), uniqid() . '.' . $img->extension());
	}
});
```

### Testowanie wysyłania wiadomości email
```php
<?php

/** @test */
function http_create_user()
{
	$pass = 'password123';

	$user = User::factory()->make();

	Event::fake([MessageSent::class]);

	$res = $this->postJson('/web/api/register', [
		'name' => $user->name,
		'email' => $user->email,
		'password' => $pass,
		'password_confirmation' => $pass,
	]);

	$res->assertStatus(201)->assertJson(['created' => true]);

	$this->assertDatabaseHas('users', [
		'name' => $user->name,
		'email' => $user->email,
	]);

	$db_user = User::where('email', $user->email)->first();

	$this->assertTrue(Hash::check($pass, $db_user->password));

	Event::assertDispatched(MessageSent::class, function ($e) {
		$html = $e->message->getHtmlBody();
		$this->assertStringContainsString("/activate", $html);
		$this->assertMatchesRegularExpression('/activate\/[0-9]+\/[a-z0-9]+\?locale\=[a-z]{2}"/i', $html);
		return true;
	});
}
```

## Api Resource with roles
```php
<?php

namespace Tests\Feature\Panel\Restaurant;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;

class RestaurantTest extends TestCase
{
	use RefreshDatabase, WithFaker;

	/** @test */
	public function list()
	{
		Restaurant::factory()->count(5)->create();

		$res = $this->get('/web/api/restaurants?perpage=2');

		$res->assertStatus(200)->assertJson([
			'restaurants' => [
				'first_page_url' => 'http://localhost/web/api/restaurants?perpage=2&page=1',
				'next_page_url' => 'http://localhost/web/api/restaurants?perpage=2&page=2',
				'per_page' => '2',
			]
		]);

		// $name = $this->faker->firstName();
	}

	/** @test */
	public function show()
	{
		$o = Restaurant::factory()->create();

		$res = $this->get('/web/api/restaurants/' . $o->id);

		$res->assertStatus(200)->assertJson([
			'restaurant' => [
				'id' => $o->id,
				'name' => $o->name,
				'notify_sms' => $o->notify_sms,
			],
		]);

		// $name = $this->faker->firstName();
	}

	/** @test */
	public function user_create_disallow()
	{
		$user = User::factory()->role('user')->create();

		$this->actingAs($user, 'web');

		$r = Restaurant::factory()->make();

		$res = $this->postJson('/web/api/restaurants', (array) $r);

		$res->assertStatus(401)->assertJson([
			'message' => 'Unauthorized.'
		]);
	}

	/** @test */
	public function worker_create_disallow()
	{
		$user = User::factory()->role('worker')->create();

		$this->actingAs($user, 'web');

		$r = Restaurant::factory()->make();

		$res = $this->postJson('/web/api/restaurants', (array) $r);

		$res->assertStatus(401)->assertJson([
			'message' => 'Unauthorized.'
		]);
	}

	/** @test */
	public function admin_create_allow()
	{
		$user = User::factory()->role('admin')->create();

		$this->actingAs($user, 'web');

		$r = Restaurant::factory()->make();

		// Create
		$res = $this->postJson('/web/api/restaurants', $r->toArray());

		$res->assertStatus(201)->assertJson([
			'message' => 'The restaurant has been created.'
		]);

		// Exists
		$this->assertDatabaseHas('restaurants', $r->toArray());
	}

	/** @test */
	public function user_update_disallow()
	{
		$user = User::factory()->role('user')->create();

		$this->actingAs($user, 'web');

		// Create
		$old = Restaurant::factory()->create();

		// New
		$r = Restaurant::factory()->make();

		// Update
		$res = $this->putJson('/web/api/restaurants/' . $old->id, $r->toArray());

		$res->assertStatus(401)->assertJson([
			'message' => 'Unauthorized.'
		]);
	}

	/** @test */
	public function worker_update_disallow()
	{
		$user = User::factory()->role('worker')->create();

		$this->actingAs($user, 'web');

		// Create
		$old = Restaurant::factory()->create();

		// New
		$r = Restaurant::factory()->make();

		// Update
		$res = $this->putJson('/web/api/restaurants/' . $old->id, $r->toArray());

		$res->assertStatus(401)->assertJson([
			'message' => 'Unauthorized.'
		]);
	}

	/** @test */
	public function admin_update_allow()
	{
		$user = User::factory()->role('admin')->create();

		$this->actingAs($user, 'web');

		// Create
		$old = Restaurant::factory()->create();

		// New
		$r = Restaurant::factory()->make();

		// Update
		$res = $this->putJson('/web/api/restaurants/' . $old->id, $r->toArray());

		$res->assertStatus(201)->assertJson([
			'message' => 'The restaurant has been updated.'
		]);

		// Exists
		$this->assertDatabaseHas('restaurants', $r->toArray());
	}

	/** @test */
	public function user_delete_disallow()
	{
		$user = User::factory()->role('user')->create();

		$this->actingAs($user, 'web');

		// Create
		$r = Restaurant::factory()->create();

		// Update
		$res = $this->delete('/web/api/restaurants/' . $r->id);

		$res->assertStatus(401)->assertJson([
			'message' => 'Unauthorized.'
		]);
	}

	/** @test */
	public function worker_delete_disallow()
	{
		$user = User::factory()->role('worker')->create();

		$this->actingAs($user, 'web');

		// Create
		$r = Restaurant::factory()->create();

		// Update
		$res = $this->delete('/web/api/restaurants/' . $r->id);

		$res->assertStatus(401)->assertJson([
			'message' => 'Unauthorized.'
		]);
	}

	/** @test */
	public function admin_delete_allow()
	{
		$user = User::factory()->role('admin')->create();

		$this->actingAs($user, 'web');

		// Create
		$r = Restaurant::factory()->create();

		// Update
		$res = $this->delete('/web/api/restaurants/' . $r->id);

		$res->assertStatus(201)->assertJson([
			'message' => 'The restaurant has been deleted.'
		]);

		// Dont exists
		$this->assertSoftDeleted('restaurants', $r->toArray());
	}
}
```

