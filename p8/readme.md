# Storage, Files
Upload plików i operacje na plikach.

## Storage local
Root directory: storage/app

#### Konfiguracja
.env lub config/filesystems.php
```sh
FILESYSTEM_DRIVER=local
```

### Utwórz linki symboliczne dla katalogów w storage/app
config/filesystems.php
```php
'links' => [
    public_path('storage') => storage_path('app/public'),
    public_path('images') => storage_path('app/images'),
],
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

#### Tworzenie pliku na lokalnym dysku
```php
<?php
// Json
$data = ['fish' => 'Rybka'];
$json = json_encode($data, JSON_PRETTY_PRINT);

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

#### Storage dysk public tworzenie pliku
```php
// Dostęp do storage/app/public
Storage::disk('local')->put('file.txt', 'Message...');
echo Storage::url('file.txt');
echo asset('storage/file.txt');

Storage::->put('avatars/1', 'Message...');
Storage::disk('s3')->put('avatars/1', 'Message...');
```

#### Wysyłanie plików formularza
```php
$path = Storage::putFile('avatars', $request->file('avatar'));
$path = Storage::putFileAs('avatars', $request->file('avatar'), 'photo.jpg');
$path = Storage::putFileAs('avatars', new File('/path/to/photo'), 'photo.jpg');
```
