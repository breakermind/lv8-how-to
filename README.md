# Jak to działa w Laravelu

1. <a href="https://github.com/breakermind/how/tree/main/p1"> Exceptions Handler </a>
2. <a href="https://github.com/breakermind/how/tree/main/p2"> Model, Factory, Seeder, Migration </a>
3. <a href="https://github.com/breakermind/how/tree/main/p3"> Controller, Policy, Request Validation, Resource </a>
4. <a href="https://github.com/breakermind/how/tree/main/p4"> Model Relations, Pivot Tables (wkrótce) </a>


### Php artisan

#### Uruchom lokalny serwer php
```sh
php artisan serv
```

#### Utwórz wszystkie klasy dla modelu
```sh
php artisan make:model Post --all
php artisan make:resource PostResource
```

#### Utwórz tylko model i migrację
```sh
php artisan make:model Area --migration
```

#### Dowiązanie symboliczne z storage do public/storage (upload plików)
```sh
php artisan storage:link
```

#### Wyświetl listę routes aplikacji
```sh
php artisan route:list
```

#### Migracja tabel do bazy danych
```sh
# Utwórz tablki
php artisan migrate
php artisan migrate:fresh
php artisan migrate:fresh --path=/database/migrations/123456_create_user_table.php --force

# Utwórz tablki do testów
php artisan --env=testing migrate
php artisan --env=testing migrate:fresh
php artisan --env=testing migrate:fresh --path=/database/migrations/123456_create_user_table.php --force
```

#### Publikacja konfiguracja pakietu
```sh
php artisan vendor:publish --provider="Webi\WebiServiceProvider.php"
php artisan vendor:publish --tag=webi-config --force
```
