# Mail
Wysyłanie wiadomości email

## Szablon wiadomości blade
```php
<h1> Test email message </h1>

{{-- <img src="{{ $message->embed($pathToImage) }}"> --}}
{{-- <img src="{{ $message->embedData($data, 'image.jpg') }}"> --}}
{{-- <img src="{{ $message->embed(url($pathToFile)) }}" alt="An inline image" /> --}}
```

## Mail klasa
```php
<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		return $this->from('noreply@localhost', 'Newsletter')->view('email.test');

		// ->with([
		//     'orderName' => $this->order->name,
		//     'orderPrice' => $this->order->price,
		// ]);

		// ->attach('/path/to/file', [
		//     'as' => 'name.pdf',
		//     'mime' => 'application/pdf',
		// ]);

		//->attachFromStorageDisk('s3', '/path/to/file');
    }
}
```

## Wyślij wiadomość
```php
<?php
use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/mail', function () {

	Mail::to('user@localhost')->send(new TestMail());

	Mail::send('email.test', ['hi' => 'hello'], function ($message) {
	  $message->subject('Test mail');
	  $message->from('noreply@localhost', 'Promotions');
	  $message->to('user@localhost');
	  $message->cc('worker@localhost');        
	});

	return 'Mail has been sent.';
	
});
```
