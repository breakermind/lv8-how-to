# Jak to działa

1. <a href="https://github.com/breakermind/how/tree/main/p1"> Exceptions Handler </a>
2. <a href="https://github.com/breakermind/how/tree/main/p2"> Model, Factory, Seeder, Migration </a>
3. <a href="https://github.com/breakermind/how/tree/main/p3"> Controller, Policy, Request Validation, Resource </a>


### Php artisan

### Uruchom lokalny serwer php
```sh
php artisan serv
```

#### Dowiązanie symboliczne ze storage do public/storage (upload plików)
```sh
php artisan storage:link
```

#### Wyświetl listę routes aplikacji
```sh
php artisan route:list
```

#### Utwórz wszystkie klasy dla modelu
```sh
php artisan make:model Post --all
php artisan make:resource PostResource
```

#### Migracja tabel do bazy danych
```sh
# Utwórz tablki
php artisan migrate
php artisan migrate:fresh
php artisan migrate:fresh --path=/databases/migrations/123456_file_name.php

# Utwórz tablki do testów
php artisan --env=testing migrate
php artisan --env=testing migrate:fresh
php artisan --env=testing migrate:fresh --path=/databases/migrations/123456_file_name.php
```

#### Publikacja konfiguracja pakietu
```sh
php artisan vendor:publish --provider="Webi\WebiServiceProvider.php"

php artisan vendor:publish --tag=webi-config --force
```
