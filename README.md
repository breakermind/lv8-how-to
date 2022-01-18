1. # Jak to działa

1. <a href="https://github.com/breakermind/how/tree/main/p1"> Exceptions Handler </a>
2. <a href="https://github.com/breakermind/how/tree/main/p2"> Model, Factory, Seeder, Migration </a>
3. <a href="https://github.com/breakermind/how/tree/main/p3"> Controller, Policy, Request Validation, Resource </a>


2. ### Php artisan

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
