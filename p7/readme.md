# Jobs, Queues, Schedulers

## Harmonogram

### Utwórz logi
```sh
cd app
mkdir -p storage/logs/cron.log
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
