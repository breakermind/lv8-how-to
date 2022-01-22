# Storage, Files
Upload plików i operacje na plikach.

## Storage local
Root directory: storage/app

### Konfiguracja
.env lub config/filesystems.php
```sh
FILESYSTEM_DRIVER=local
```

### Link symboliczny z /public/storage do /storage/app/public
Publiczny dostęp do plików wysyłanych na server.
```sh
php artisan storage:link
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

#### Storage klasa do tworzenia pliku
```php

```
