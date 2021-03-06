# Storage, Files, Upload
Upload plików i operacje na plikach.

## Storage katalog główny (local)
Directory: storage/app
```sh
# Uprawnienia upload
sudo chown -R username:www-data /home/username/www/app.xx
sudo chown -R www-data:username /home/username/www/app.xx/storage
sudo chmod -R 2775 /home/username/www/app.xx
```

### Utwórz linki symboliczne
Publiczny dostęp do plików wysyłanych na server z katalogu głównego aplikacji storage/app/public.
```sh
php artisan storage:link

# Tworzy link
public/storage <=> storage/app/public

# Wtedy plik z
storage/app/public/json/file.json

# Wyświetlamy jako
public/storage/json/file.json

# Tworzymy url do pliku
echo asset('json/file.json');

# Default Storage::put() lub disk('local') wskazuje na
storage/app
```

### W skrócie
```php
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

// Utwórz plik: storage/app/local.txt => not accessible from root app /public
Storage::put('local.txt', 'Local disc');

// Utwórz plik: storage/app/public/local.txt => /public/storage/public.txt
Storage::disk('local')->put('public/local-public.txt', 'Local disc');

// Utwórz plik: storage/app/public/public.txt => /public/storage/public.txt
Storage::disk('public')->put('public.txt', 'Public disc');

// Utwórz plik: storage/app/public/gallery/public.txt => /public/storage/gallery/public.txt
Storage::disk('public')->put('gallery/public.txt', 'Public disc');

// Utwórz storage/app/avatars/favicon.ico
echo $path = Storage::putFileAs('avatars', new File(base_path().'/public/favicon.ico'), 'favicon.ico');
// avatars/favicon.ico

// Utwórz storage/app/public/avatars/favicon.ico
echo $path = Storage::disk('public')->putFileAs('avatars', new File(base_path().'/public/favicon.ico'), 'favicon.ico');
// avatars/favicon.ico

// Upload pliku: storage/app/public/avatars/{hashed-filename}.png
echo $path = Storage::disk('public')->putFile('avatars', request()->file('avatar'));

// Upload pliku: storage/app/public/avatars/image.png
echo $path = Storage::disk('public')->putFileAs('avatars', request()->file('avatar'), 'image.png');

// Po dodaniu nowego dysku 'images' w config/filesystems.php

// Utwórz z upload storage/app/public/avatars/{hashed-filename}.png
echo $path = Storage::disk('public')->putFile('avatars', request()->file('avatar'));

// Utwórz z upload storage/app/public/avatars/image.png
echo $path = Storage::disk('public')->putFileAs('avatars', request()->file('avatar'), 'image.png');	
```

#### Upload informacje o pliku
```php
$file = $request->file('avatar');
$name = $file->getClientOriginalName();
$extension = $file->getClientOriginalExtension();
$extension_mime = $file->extension();
$name = $file->hashName();
```

#### Walidacja pliku
```php
$validated = request()->validate([    
    'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
    'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:4096',
    'title' => ['sometimes', 'max:255'],
]);

if ($request->hasFile('image'))
{
    // Save image
    $path = request()->file('image')->store('gallery', 'public');
       
    // Save custom file
    $ext = request()->file('image')->extension();
    $path = request()->file('image')->storeAs('gallery', uniqid().'.'.$ext, 'public');
    
    // Dodaj ścieżkę
    request()->merge(['image' => $path]);
    
    // Save in database
    Gallery::create(request()->only(['image', 'title']));
    
    // Resize image file path
    $storage_path = storage_path('app/public/') . $path;
    
    // Resize image here
}
```

#### Wyświetlanie urls
```php
<?php
# Tworzymy url do pliku
echo asset('storage/file.txt');

# Tworzymy url do pliku
echo asset('images/ico.png');
```

#### Tworzenie plików tradycyjnie
```php
<?php
// Json
$data = ['fish' => 'Rybka'];
$json = json_encode($data, JSON_PRETTY_PRINT);

// Katalog główny aplikacji
$path = base_path('resource/lang/') . 'pl.json';
file_put_contents($path, $json);

// Katalog /resource/lang/pl.json
$path = resource_path('lang/') . 'pl.json';
file_put_contents($path, $json);

// Katalog /public/settings/pl.json
$path = public_path('settings/') . 'pl.json';
file_put_contents($path, $json);

// Katalog /storage/app/lang/pl.json
$path = storage_path('app/public/lang/') . 'pl.json';
file_put_contents($path, $json);
```

## Konfiguracja
.env lub config/filesystems.php
```sh
FILESYSTEM_DRIVER=local
```

#### Utwórz linki symboliczne dla katalogów w storage/app
config/filesystems.php
```php
'links' => [
    public_path('storage') => storage_path('app/public'),
    public_path('images') => storage_path('app/images'),
],

'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'permissions' => [
                'file' => [
                    'public' => 2775,
                    'private' => 0600,
                ],
                'dir' => [
                    'public' => 2775,
                    'private' => 0700,
                ],
            ],
        ],
        
        'images' => [
            'driver' => 'local',
            'root' => storage_path('app/images'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],
];
```

#### Utwórz linki symboliczne
Publiczny dostęp do plików wysyłanych na server.
```sh
php artisan storage:link

# Daje linki do 
/public/storage => /storage/app/public
/public/images => /storage/app/images
```

#### Ścieżki do plików
```php
<?php
// public/storage/file.txt
echo asset('storage/file.txt');

// public/images/ico.png
echo asset('images/ico.png');
```

#### Storage dysk public tworzenie pliku
```php
use Illuminate\Support\Facades\Storage;

// Katalog storage/app
Storage::disk('local')->put('file.txt', 'Message...');

// Katalog storage/app/public
Storage::disk('public')->put('file.txt', 'Message...');

// Katalog storage/app/images
Storage::disk('images')->put('file.txt', 'Message...');

// File path /storage/...
echo Storage::url('file.txt');
echo asset('storage/file.txt');

// Storage images
echo Storage::disk('images')->url('file.txt');

Storage::->put('avatars/1', 'Message...');
Storage::disk('s3')->put('avatars/1', 'Message...');
```

#### Zapisywanie plików z formularza
```php
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

$path = $request->file('avatar')->store('avatars');
$path = $request->file('avatar')->storeAs('avatars', $request->user()->id);

$path = Storage::putFile('avatars', $request->file('avatar'));
$path = Storage::putFileAs('avatars', $request->file('avatar'), $request->user()->id);

$path = Storage::putFileAs('avatars', $request->file('avatar'), 'photo.jpg');
$path = Storage::putFileAs('avatars', new File('/path/to/photo'), 'photo.jpg');
```

#### Zapisywanie plików na dysk s3 aws
```php
$path = $request->file('avatar')->store('avatars/'.$request->user()->id, 's3');
$path = $request->file('avatar')->storeAs('avatars', $request->user()->id, 's3');
```

#### Usuwanie plikuów
```php
Storage::delete('file.jpg');

Storage::delete(['file.jpg', 'file2.jpg']);

Storage::disk('s3')->delete('path/file.jpg');
```

## Przykłady

### Instalacja Intervention Image 
Dokumentacja na https://image.intervention.io/v2
```sh
composer require intervention/image
```

### Dodaj w .env, .env.testing
Zmień główny (default) dysk na public
```sh
FILESYSTEM_DISK=public
```

### Wysyłanie zdjęcia na serwer i zmiana rozmiaru
```php

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRequest;
use Intervention\Image\ImageManagerStatic as Image;

class AuthController extends Controller
{
    function update(UpdateRequest $r)
	{
		$valid = $r->validated();

		if(Auth::check()) {
			try {
				$user = Auth::user();

				if($r->hasFile('avatar'))
				{
					$allowed = array_diff(
						['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'],
						[$r->file('avatar')->extension()]
					);

					foreach ($allowed as $ext) {
						$f = 'avatars/' . $user->id . '.' . $ext;
						if (Storage::exists($f)) {
							Storage::delete($f); // Delete old images
						}
					}

					$path = Storage::putFileAs(
						'avatars',
						$r->file('avatar'),
						$user->id . '.' . $r->file('avatar')->extension()
					);

					if (Storage::exists($path)) {
						$valid['avatar'] = $path;
						Image::make(Storage::path($path))->resize($this->width, $this->height)->save();
					} else {
						unset($valid['avatar']);
					}
				}

				User::where([
					'id' => $user->id
				])->update($valid);

			} catch (Exception $e) {
				report($e);
				throw new Exception(trans('Update error'), 422);
			}
		}

		return response()->json([
			'message' => trans('Profil has been updated'),
			'user' => Auth::user()->fresh()
		]);
	}
}
```

### Walidacja danych
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
			'mobile' => 'sometimes|max:20|regex:/^[\+]{0,1}[0-9]+$/',
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
