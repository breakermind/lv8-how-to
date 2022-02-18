# Jak to działa w Laravel

0. <a href="https://github.com/breakermind/how/tree/main/p0"> Configuration, database, .env </a>
1. <a href="https://github.com/breakermind/how/tree/main/p1"> Exceptions Handler, Middleware Roles, CSRF Token </a>
2. <a href="https://github.com/breakermind/how/tree/main/p2"> Model, Factory, Seeder, Migration </a>
3. <a href="https://github.com/breakermind/how/tree/main/p3"> Controller, Policy, Request Validation, Resource </a>
4. <a href="https://github.com/breakermind/how/tree/main/p4"> Model Relations, Pivot Tables </a>
5. <a href="https://github.com/breakermind/how/tree/main/p5"> Events, Listeners </a>
6. <a href="https://github.com/breakermind/how/tree/main/p6"> Database Translations </a>
7. <a href="https://github.com/breakermind/how/tree/main/p7"> Jobs, Queues, Schedulers </a>
8. <a href="https://github.com/breakermind/how/tree/main/p8"> Storage, Files, Upload </a>
9. <a href="https://github.com/breakermind/how/tree/main/p9"> Testing, PHPUnit, Image Upload, Image Resize </a>
10. <a href="https://github.com/breakermind/how/tree/main/p10"> ServiceProvider, Registry Pattern </a>

### Composer, git

#### Utwórz project
```sh
composer create-project laravel/laravel app-dir
```

#### Pobierz wybrany tag/version z repozytorium
```sh
git clone --branch <tag-version> <repo-url> <app-dir>
git clone --branch v1.0.1 https://github.com/breakermind/lv8-how-to.git samples

git clone --branch <tag-version> --depth=1 <repo-url> <app-dir>
git clone --branch <tag-version> --single-branch <repo-url> <app-dir>
git clone --branch v1.0.3 --single-branch https://github.com/breakermind/lv8-how-to.git samples
```

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

#### Utwórz route middleware
```sh
php artisan make:middleware UserRoles
```

#### Utwórz email
```sh
php artisan make:mail NewsletterMail
```

#### Dowiązanie symboliczne z storage do public/storage (upload plików)
```sh
php artisan storage:link
```

#### Wyświetl listę routes aplikacji
```sh
php artisan route:list
```

#### Utwórz migrację modelu
```sh
php artisan make:migration update_user_table
```

#### Utwórz queue tabelę
```sh
php artisan queue:table
php artisan migrate

# php artisan queue:failed-table
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

### Session w bazie danych
config/session.php
```sh
php artisan session:table
php artisan migrate
```

#### Harmonogram pracy (cron job worker)
```sh
php artisan schedule:work
```

#### Publikacja konfiguracja pakietu
```sh
php artisan vendor:publish --provider="Webi\WebiServiceProvider.php"
php artisan vendor:publish --tag=webi-config --force
```

#### Run php artisan commands with composer.json
composer dump-autoload -o
```json
{
	"scripts": {
		"post-autoload-dump": [
			"@php artisan storage:link --ansi"
		],
	}	
}
