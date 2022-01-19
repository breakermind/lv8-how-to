# Events, Listeners
Zdarzenia i powiadomienia zdarzeń.

#### Utwórz zdarzenia
```sh
php artisan make:event OrderShipped

php artisan make:listener SendShipmentNotification --event=OrderShipped
```

#### Dodaj zdarzenia
app\Providers\EventServiceProvider.php
```php
use App\Events\OrderShipped;
use App\Listeners\SendShipmentNotification;

public function shouldDiscoverEvents()
{
    return true;
}

protected $listen = [
    OrderShipped::class => [
        SendShipmentNotification::class,
    ],
];
```

#### Utwórz zdarzenie
```php
<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderShipped
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public $order;

	public function __construct(Order $order)
	{
		  $this->order = $order;
	}
}
```

#### Utwórz powiadomienie na zdarzenie
```php
<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendShipmentNotification // implements ShouldQueue
{
	// use InteractsWithQueue;

	protected $event_user;

	public function __construct(User $user) {
		// $this->event_user = $user;
	}

	public function handle(OrderShipped $event)
	{
		// Access the order using $event->order
	}
	
	public function failed(OrderShipped $event, $exception)
    {
        //
    }
}
```

#### Wywołanie zdarzenia
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\OrderShipped;
use App\Models\Order;

class OrderShipmentController extends Controller
{        
	public function store(Request $request)
	{
		$order = Order::findOrFail($request->order_id);

		// Order shipment logic...

		OrderShipped::dispatch($order);
	}
}
```
