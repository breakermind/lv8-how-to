# Jobs, Queues, Schedulers
Harmonogram zadań z zadaniami i kolejkowaniem wiadomości email.

## Schedulers

### Utwórz logi
```sh
cd app
mkdir -p storage/logs/cron.log
mkdir -p storage/logs/queue.log
mkdir -p storage/logs/newsletter.log
```

### Uruchom harmonogram z cron
sudo crontab -e
```sh
# without logs
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

# with logs
* * * * * cd /path-to-your-project && php artisan schedule:run >> /home/www/app.xx/storage/logs/cron.log
```

### Uruchom harmonogram lokalnie (dev)
```sh
php artisan schedule:work

php artisan schedule:list
```

### Dodaj zadanie do harmonogramu
app/Console/Kernel.php
```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendEmailNewsletterJob;

class Kernel extends ConsoleKernel
{  
	protected function schedule(Schedule $schedule)
	{
		// Send newsletter
		$schedule->job(new SendEmailNewsletterJob())->daily();
	}
}
```

## Jobs

### Utwórz zadanie
```sh
php artisan make:job SendEmailNewsletterJob
```

### Edytuj zadanie
```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterMail;
use App\Models\User;

class SendEmailNewsletterJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function handle()
	{
		$uid = uniqid();
		
		$users = User::where('newsletter_on', 1)->get();

		$channel = Log::build([
			'driver' => 'single',
			'path' => storage_path('logs/newsletter.log'),
		]);

		if(count($users) > 0) {
			Log::stack([$channel])->info('('.$uid.') EmailNewsletterJob started at ' . now());

			foreach ($users as $user) {
				if(env('APP_DEBUG')) {
					Log::stack([$channel])->info('('.$uid.') Send email: ' . json_encode($user));
				}

				Mail::to($user)
					->locale($user->locale ?? 'en')
					->send(new NewsletterMail($user));
			}
		} else {
			if(env('APP_DEBUG')) {
				Log::stack([$channel])->info('('.$uid.') There is no users for newsletter.');
			}
		}
	}
}
```

### Utwórz wiadomość email
```sh
php artisan make:mail NewsletterMail
```

### Edytuj wiadomość
app/Mail/NewsletterMail.php
```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable implements ShouldQueue
{
	use Queueable, SerializesModels;

	public $user;
	public $subject;
	public $view;

	public function __construct(object $user, $view = 'emails.newsletter.mail', $subject = 'Fresh newsletter', $nam = 'Newsletter')
	{
		$this->user = $user;
		$this->from(env('MAIL_FROM_ADDRESS') ?? 'no-reply@'.request()->getHttpHost(), $name);
		$this->subject(trans('messages.emails.newsletter.subject') ?? $subject);
		$this->view($view);
		
		$this->afterCommit();
	}

	public function build()
	{
		return $this;
	}
}
```

### Szablon blade wiadomości
resource/views/emails/newsletter/mail.blade.php
```php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>@lang('Fresh newsletter')</title>

</head>
<body>
	<h1>Welcome {{ $user->name }}!</h1>
	<p>We have got new promotion ... <a href="/promotions"> Click here </a></p>
	<p>Regards, Thank you for subscribing!</p>
	<p>Date: <small>{{ now() }}</small></p>
</body>
</html>
```

### Utwórz migrację
```sh
php artisan make:migrate  update_users_table
```

### Migracja tabeli usera
database/migrations/2022_01_21_1234567_update_users_table.php
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
			if (!Schema::hasColumn('users', 'locale')) {
				$table->string('locale', 2)->nullable()->default('pl');
			}
			if (!Schema::hasColumn('users', 'newsletter_on')) {
				$table->tinyInteger('newsletter_on')->nullable()->default(1);
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

### Uruchom migrację
```sh
php artisan migrate
```

## Queues

### Utwórz tabelki w bazie danych
```sh
php artisan queue:table
php artisan migrate
```

### Zainstaluj monitoring service
```sh
sudo apt-get install supervisor

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Uruchom kolejkę jako service
/etc/supervisor/conf.d/laravel-worker.conf 
```sh
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/forge/app.xx/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=forge
numprocs=8
redirect_stderr=true
stdout_logfile=/home/forge/app.xx/storage/logs/queue.log
stopwaitsecs=3600

# Or with Aws
# command=php /home/forge/app.xx/artisan queue:work sqs --sleep=3 --tries=3 --max-time=3600
```

### Uruchom przetwarzanie kolejki lokalnie w terminalu (dev)
```sh
php artisan queue:work
```
