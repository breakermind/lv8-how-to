# Mail, User
Wysyłanie wiadomości email

## Szablon wiadomości html
```php
<h1> Test email message </h1>

{{-- <img src="{{ $message->embed($pathToImage) }}"> --}}
{{-- <img src="{{ $message->embedData($data, 'image.jpg') }}"> --}}
{{-- <img src="{{ $message->embed(url($pathToFile)) }}" alt="An inline image" /> --}}
```

## Szablon blade z markdown
```php
@component('mail::message')
# Order Shipped
 
Your order has been shipped!
 
@component('mail::button', ['url' => $url, 'color' => 'success'])
View Order
@endcomponent
 
Thanks,<br>
{{ config('app.name') }}
@endcomponent
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

	public $data = null;
	
	/**
	* Create a new message instance.
	*
	* @return void
	*/
	public function __construct($data = null)
	{
		$this->data = $data;
	}

	/**
	* Build the message.
	*
	* @return $this
	*/
	public function build()
	{
		return $this->from('noreply@localhost', 'Newsletter')->view('email.test');
		
		// ->text('email.test_plain');		
		// ->markdown('emails.orders.shipped', ['url' => $this->order->url, ]);
		
		// ->tag('shipment')->metadata('order_id', $this->order->id);

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

	Mail::to('user@localhost')->locale('en')->send(new TestMail());

	Mail::send('email.test', ['hi' => 'hello'], function ($message) {
	  $message->subject('Test mail');
	  $message->from('noreply@localhost', 'Promotions');
	  $message->to('user@localhost');
	  $message->cc('worker@localhost');        
	});

	// Mail::mailer('mailgun')->to('user@localhost')->cc($moreUsers)->bcc($evenMoreUsers)->send(new TestMail());
	// ->queue(new TestMail());
	// ->later(now()->addMinutes(10), new TestMail());
	
	return 'Mail has been sent.';
	
});
```

## User preferowany język wiadomości
```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Translation\HasLocalePreference;

class User extends Authenticatable implements MustVerifyEmail, HasLocalePreference
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the user's preferred locale.
     *
     * @return string
     */
    public function preferredLocale()
    {
        return $this->locale;
    }
}
```
