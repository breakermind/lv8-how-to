# Service Provider, Registry Pattern
Dynamiczne dodawanie zależności i wzorce.

### Service Provider
```php
<?php

namespace App\Providers;

use App\Services\Riak\Connection;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{    
    public function register()
    {
		// Payment gateway Registery Pattern
		$this->app->singleton(PaymentGatewayRegistry::class);
    }
    
    public function boot()
    {
		// Add payment gateways to registry list (manualy or dynamicaly with class loader)
		$this->app->make(PaymentGatewayRegistry::class)->register("payu", new PayuPaymentGateway());
		// $this->app->make(PaymentGatewayRegistry::class)->register("paypal", new PayPalPaymentGateway());
    }
}
```

### Registery pattern Controller
```php
<?php

// Custom service controller
class PaymentController extends Controller
{
	// Not allowd gateway names
	protected $defaultGateways = ['pay_money', 'pay_card', 'pay_pickup'];

	// Dependency injection (singleton)
	function __construct(PaymentGatewayRegistry $reg)
	{
		$this->gatewayRegistry = $reg;
	}

	function create($gateway = 'payu') 
	{
		$order = json_decode(request()->getContent());
		$gateway = $gateway ?? 'pay_money';
		$user = Auth::user() ?? null;

		$url = $this->gatewayRegistry->get($gateway)->pay($order, $user);
	}
}
```

### Dynamiczne dodawanie/wstrzykiwanie zależności
```php
<?php
public function register()
    {
		// Dependency Injection
		$this->app->bind(FileEmail::class, function($app) {
			return new FireEmail(config('app.locale'));
		});
		
		// Bind class facades blade
		$this->app->bind('fire-email', function($app) {
			return new FireEmail();
		});
		
		// Enable service with route param
		if(request()->has('service_name')) {
			if(request()->get('service_name') == 'external') {
				$this->app->bind(ServiceInterface::class, function() {
					// Change service dynamically here
					if(config('local-package.file_extension') == 'json') {
						return new ServiceJson(new Translator(config('app.locale')));
					} else {
						return new ServiceCsv(new Translator(config('app.locale')));
					}
				});
			}
		}
		
		// Events
		$this->app->resolving(function ($object, $app) {
			// Called when container resolves object of any type...
		});
		
		// Add controller
		Route::group([
			// 'prefix' => config('fire-email.prefix', 'fire-email'),
			// 'middleware' => config('fire-email.middleware', ['web','api']),
		], function () {
			$this->app->make('App\Email\EmailController');
		});
    }
}
```
