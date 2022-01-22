# Storage, Files, Upload
Upload plików i operacje na plikach.

## Storage katalog główny (local)
Directory: storage/app

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
```

### W skrócie
```php
// Utwórz plik: storage/app/local.txt => not accessible from root app /public
Storage::disk('local')->put('local.txt', 'Local disc');

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
    request()->merge['image' => $path];
    
    // Save in database
    Gallery::create(request->only(['image', 'title']));
    
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
